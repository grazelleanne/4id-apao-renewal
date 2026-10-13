<?php
namespace App\Services;
final class ImportReader {
 public const LIMIT=1000;
 public bool $clientWorkbook=false;
 public function read(string $file,string $extension): array {
  $this->clientWorkbook=false;
  $rows=in_array($extension,['xlsx','xlsm'],true)?$this->xlsx($file,$extension==='xlsm'):$this->csv($file);
  if(count($rows)<2)throw new \InvalidArgumentException('Upload a header row and at least one data row.');
  $header=array_map(static fn($v)=>strtolower(trim(ltrim((string)$v,"\xEF\xBB\xBF"))),array_shift($rows));
  if(count(array_unique($header))!==count($header)||in_array('',$header,true))throw new \InvalidArgumentException('Headers must be nonempty and unique.');
  $result=[];
  foreach($rows as $index=>$row){
   if(!array_filter($row,static fn($v)=>trim((string)$v)!==''))continue;
   if(count($row)>count($header))throw new \InvalidArgumentException('Row '.($index+2).' contains more cells than the header.');
   $result[]=['line'=>$index+2,'values'=>array_combine($header,array_pad($row,count($header),''))];
  }
  return $result;
 }
 private function csv(string $file): array {
  $stream=fopen($file,'rb');$rows=[];
  try{while(($row=fgetcsv($stream,0,',','"',''))!==false){$rows[]=$row;if(count($rows)>self::LIMIT+1)throw new \InvalidArgumentException('Maximum 1000 records per upload.');}}
  finally{fclose($stream);}return $rows;
 }
 private function xml(string $text): \SimpleXMLElement {
  if(stripos($text,'<!DOCTYPE')!==false||stripos($text,'<!ENTITY')!==false)throw new \InvalidArgumentException('Unsafe XML in workbook.');
  $prior=libxml_use_internal_errors(true);
  try{$xml=simplexml_load_string($text,\SimpleXMLElement::class,LIBXML_NONET);if($xml===false)throw new \InvalidArgumentException('Invalid workbook XML.');return $xml;}
  finally{libxml_clear_errors();libxml_use_internal_errors($prior);}
 }
 private function xlsx(string $file,bool $macroWorkbook=false): array {
  $zip=SpreadsheetArchive::open($file);
  try{
   $total=0;for($i=0;$i<$zip->numFiles;$i++){$s=$zip->statIndex($i);$total+=$s['size'];}
   if($total>20000000||$zip->numFiles>2000)throw new \InvalidArgumentException('Workbook is too large when expanded.');
   $strings=[];$shared=$zip->getFromName('xl/sharedStrings.xml');
   if($shared!==false){$xml=$this->xml($shared);foreach($xml->xpath('//*[local-name()="si"]') as $si){$value='';foreach($si->xpath('.//*[local-name()="t"]') as $t)$value.=(string)$t;$strings[]=$value;}}
   $workbook=$zip->getFromName('xl/workbook.xml');$relations=$zip->getFromName('xl/_rels/workbook.xml.rels');
   if($workbook===false||$relations===false)throw new \InvalidArgumentException('Workbook metadata is missing.');
   $sheets=$this->xml($workbook)->xpath('//*[local-name()="sheet"]');
   if(!$sheets)throw new \InvalidArgumentException('Workbook has no sheets.');
   $selected=$sheets[0];$clientSheet=false;
   foreach($sheets as $candidate)if(in_array((string)$candidate['name'],['Database for Pistol, 9mm Glock','Pistol Records'],true)){$selected=$candidate;$clientSheet=true;$this->clientWorkbook=true;break;}
   $rid=(string)$selected->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
   $target=null;foreach($this->xml($relations)->xpath('//*[local-name()="Relationship"]') as $r){if((string)$r['Id']===$rid&&(string)$r['TargetMode']!=='External')$target=(string)$r['Target'];}
   if(!$target||str_contains($target,'..')||str_contains($target,':'))throw new \InvalidArgumentException('Invalid worksheet target.');
   $sheet=$zip->getFromName(str_starts_with($target,'/')?ltrim($target,'/'):'xl/'.$target);
   if($sheet===false)throw new \InvalidArgumentException('First worksheet is missing.');
   $rows=[];$clientColumns=[];foreach($this->xml($sheet)->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') as $r){
    $cells=[];foreach($r->xpath('./*[local-name()="c"]') as $c){
     if(!$clientSheet && $c->xpath('./*[local-name()="f"]'))throw new \InvalidArgumentException('Formula cells are unsupported. Paste values before importing.');
     preg_match('/^([A-Z]+)[0-9]+$/',(string)$c['r'],$m);if(!$m)throw new \InvalidArgumentException('Invalid cell address.');
     $col=0;foreach(str_split($m[1]) as $letter)$col=$col*26+ord($letter)-64;
     if($clientSheet && (int)$r['r']>1 && !isset($clientColumns[$col-1]))continue;
     if($col>100)throw new \InvalidArgumentException('Maximum 100 columns.');
     $values=$c->xpath('./*[local-name()="v"]');$value=isset($values[0])?(string)$values[0]:'';
     if((string)$c['t']==='s')$value=$strings[(int)$value]??'';
     if((string)$c['t']==='inlineStr'){$value='';foreach($c->xpath('.//*[local-name()="t"]') as $t)$value.=(string)$t;}
     $cells[$col-1]=$value;
    }
    $row=[];if($cells)for($i=0;$i<=max(array_keys($cells));$i++)$row[]=$cells[$i]??'';
    if($clientSheet && !$clientColumns){
     $aliases=['item nr'=>'item_number','date of validity'=>'date_of_validity','date of birth'=>'date_of_birth','nomenclature of pistol'=>'pistol_nomenclature','serial nr'=>'pistol_serial_number','qty of ammo'=>'qty_ammo','unit'=>'unit','afp serial nr'=>'afp_serial_number','first name'=>'first_name','last name'=>'last_name','middle name'=>'middle_name','rank'=>'rank','complete name'=>'complete_name','email'=>'email','email address'=>'email','atp serial number'=>'atp_serial_number','issued by'=>'issued_by','pistol type'=>'pistol_type','status'=>'import_status'];
     foreach($row as $col=>$label){$label=strtolower(trim(preg_replace('/\s+/',' ',(string)$label)));foreach($aliases as $prefix=>$field)if($label===$prefix||str_starts_with($label,$prefix.' (')){$clientColumns[$col]=$field;break;}}
     if(!isset($clientColumns[0])||$clientColumns[0]!=='item_number')throw new \InvalidArgumentException('Client data worksheet has unsupported headers. Use the import template.');
     $rows[]=['item_number','first_name','last_name','middle_name','rank','unit','email','date_of_birth','afp_serial_number','atp_serial_number','pistol_nomenclature','pistol_serial_number','pistol_type','issued_by','qty_ammo','date_of_validity','import_status'];continue;
    }
    if($clientSheet){
     $values=array_fill_keys($rows[0],'');foreach($clientColumns as $col=>$field)$values[$field]=$cells[$col]??'';
     if(trim((string)$values['item_number'])==='')continue;
     foreach(['date_of_birth','date_of_validity'] as $field)if(is_numeric($values[$field])&&(float)$values[$field]>0){
      $date1904=(string)($this->xml($workbook)->workbookPr['date1904']??'0');
      $epoch=in_array($date1904,['1','true'],true)?'1904-01-01':'1899-12-30';
      $values[$field]=(new \DateTimeImmutable($epoch))->modify('+'.(int)floor((float)$values[$field]).' days')->format('Y-m-d');
     }
     if(!empty($values['complete_name'])){
      // Only the unambiguous RANK FIRST LAST AFP-SERIAL pattern is split automatically.
      if(preg_match('/^(\S+)\s+([A-Za-zÀ-ÿ\x{2019}\x{0027}-]+)\s+([A-Za-zÀ-ÿ\x{2019}\x{0027}-]+)\s+([A-Za-z]?-\d+)\b/u',trim($values['complete_name']),$name)){
       $values['rank']=$values['rank']?:$name[1];$values['first_name']=$values['first_name']?:$name[2];$values['last_name']=$values['last_name']?:$name[3];$values['afp_serial_number']=$values['afp_serial_number']?:$name[4];
      }
      unset($values['complete_name']);
     }
     if(!$values['pistol_type'] && preg_match('/\b(9mm|\.45|45)\b/i',$values['pistol_nomenclature'],$caliber))$values['pistol_type']=$caliber[1];
     $row=array_values($values);
    }
    $rows[]=$row;if(count($rows)>self::LIMIT+1)throw new \InvalidArgumentException('Maximum 1000 records per upload.');
   }return $rows;
  }finally{$zip->close();}
 }
}
