<?php
namespace App\Http\Controllers;
use App\Services\ParService;
use App\Models\Personnel;
use App\Models\PropertyAcknowledgementReceipt as Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
final class ParController extends ActionController
{
    public function index(){return $this->action(fn()=>ParService::par_list());}
    public function print(int $item){return $this->action(fn()=>ParService::par_pdf($item));}
    public function data()
    {
        $state=[];
        foreach (Receipt::with('personnel')->orderBy('id')->get() as $r) {
            if (!$r->personnel || $r->personnel->archived_at) continue;
            $state[$r->personnel->item_number]=['parStatus'=>'issued','parNumber'=>$r->par_number,
                'dateIssued'=>$r->issued_date?->format('Y-m-d'),'issuedBy'=>$r->issued_by,
                'approvedBy'=>$r->approved_by,'issuedBySignature'=>$r->issued_by_signature,
                'approvedBySignature'=>$r->approved_by_signature,'wasReplaced'=>(bool)$r->previous_par_id];
        }
        $activity=\App\Models\AuditLog::whereIn('action',['par_issue','par_update','par_replace','par_reprint'])
            ->latest('id')->limit(200)->get()->map(function($log) {
                $record=Receipt::with('personnel')->where('par_number',$log->target)->first();
                return ['ts'=>$log->created_at?->toIso8601String(),'personnel'=>$record?->personnel
                    ?trim($record->personnel->first_name.' '.$record->personnel->last_name):'',
                    'action'=>['par_issue'=>'PAR Issued','par_update'=>'PAR Updated','par_replace'=>'PAR Replaced','par_reprint'=>'PAR Reprinted'][$log->action],
                    'parNumber'=>$log->target,'by'=>$log->user_name];
            });
        return response()->json(['success'=>true,'state'=>(object)$state,'activity'=>$activity]);
    }
    public function reprint(int $item) {
        $p=Personnel::where('item_number',$item)->whereNull('archived_at')->firstOrFail();
        $r=Receipt::where('personnel_id',$p->id)->latest('id')->firstOrFail();
        audit($this->account(),'par_reprint',$r->par_number);
        return response()->json(['success'=>true]);
    }
    public function save(Request $request,int $item)
    {
        $data=$request->validate(['mode'=>'required|in:issue,update,replace','parNumber'=>'required|string|max:255',
            'dateIssued'=>'required|date|before_or_equal:today','issuedBy'=>'required|string|max:255',
            'approvedBy'=>'required|string|max:255','issuedBySignature'=>'nullable|string|max:1000000',
            'approvedBySignature'=>'nullable|string|max:1000000','remarks'=>'nullable|string|max:500']);
        foreach (['issuedBySignature','approvedBySignature'] as $field) {
            if (!empty($data[$field]) && !preg_match('/^data:image\/(png|jpeg);base64,[A-Za-z0-9+\/=]+$/',$data[$field]))
                return response()->json(['message'=>'Provide a valid signature image.'],422);
        }
        $record=DB::transaction(function() use($data,$item) {
            $p=Personnel::where('item_number',$item)->whereNull('archived_at')->lockForUpdate()->firstOrFail();
            $previous=Receipt::where('personnel_id',$p->id)->latest('id')->lockForUpdate()->first();
            if ($data['mode']==='issue' && $previous) abort(409,'A PAR already exists. Refresh and use Update.');
            if ($data['mode']!=='issue' && !$previous) abort(409,'Issue a PAR before updating or replacing it.');
            if ($data['mode']==='issue' && strtolower((string)$p->ics_status)!=='ready') abort(409,'Inspection approval is required before PAR issuance.');
            $duplicate=Receipt::where('par_number',$data['parNumber']);
            if ($data['mode']==='update') $duplicate->where('id','!=',$previous->id);
            if ($duplicate->exists()) abort(409,'That PAR number is already in use.');
            $r=$data['mode']==='update'?$previous:new Receipt;
            $r->fill(['personnel_id'=>$p->id,'par_number'=>$data['parNumber'],'unit'=>$p->unit,
                'firearm'=>$p->pistol_nomenclature?:'Pistol','firearm_serial_number'=>$p->pistol_serial_number,
                'firearm_quantity'=>1,'firearm_unit_cost'=>35000,'ammunition_quantity'=>$p->qty_ammo,
                'ammunition_unit_cost'=>22,'status'=>'Active','issued_date'=>$data['dateIssued'],
                'valid_until'=>$p->date_of_validity,'issued_by'=>$data['issuedBy'],'approved_by'=>$data['approvedBy'],
                'issued_by_signature'=>$data['issuedBySignature']??null,'approved_by_signature'=>$data['approvedBySignature']??null,
                'receiver_signature'=>$p->signature,'remarks'=>$data['remarks']??null,'updated_by'=>$this->account()['id']]);
            if (!$r->exists) $r->created_by=$this->account()['id'];
            if ($data['mode']==='replace') {
                $r->previous_par_id=$previous->id;
                $previous->update(['status'=>'Replaced','replaced_at'=>now(),'updated_by'=>$this->account()['id']]);
            }
            $r->save(); $p->update(['par_number'=>$r->par_number]);
            audit($this->account(),'par_'.$data['mode'],$r->par_number);
            return $r;
        });
        return response()->json(['success'=>true,'id'=>$record->id]);
    }
}
