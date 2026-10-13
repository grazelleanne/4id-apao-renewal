<?php
namespace App\Services;
final class SpreadsheetArchive {
 public int $numFiles=0;
 private ?\ZipArchive $zip=null;
 private ?\PharData $phar=null;
 private array $entries=[];
 private ?string $temporary=null;
 public static function open(string $file): self {
  $archive=new self;
  if(class_exists(\ZipArchive::class)){
   $archive->zip=new \ZipArchive();
   if($archive->zip->open($file)!==true)throw new \InvalidArgumentException('Invalid Excel workbook.');
   $archive->numFiles=$archive->zip->numFiles;
  }else{
   if(!class_exists(\PharData::class))throw new \InvalidArgumentException('Enable PHP zip or Phar to read Excel files.');
   $base=tempnam(sys_get_temp_dir(),'apao_excel_');
   if($base===false)throw new \RuntimeException('Unable to prepare workbook.');
   $archive->temporary=$base.'.zip';unlink($base);
   try{
    if(!copy($file,$archive->temporary))throw new \RuntimeException('Unable to read upload.');
    $archive->phar=new \PharData($archive->temporary);
    foreach(new \RecursiveIteratorIterator($archive->phar) as $entry){$archive->entries[]=['size'=>$entry->getSize()];if(count($archive->entries)>2000)throw new \InvalidArgumentException('Workbook has too many entries.');}
    $archive->numFiles=count($archive->entries);
   }catch(\Throwable $e){$archive->close();throw new \InvalidArgumentException('Invalid or unsupported Excel archive.',0,$e);}
  }
  return $archive;
 }
 public function statIndex(int $index): array {return $this->zip?$this->zip->statIndex($index):$this->entries[$index];}
 public function getFromName(string $name): string|false {
  if($this->zip)return $this->zip->getFromName($name);
  return isset($this->phar[$name])?$this->phar[$name]->getContent():false;
 }
 public function close(): void {
  if($this->zip){$this->zip->close();$this->zip=null;}
  $this->phar=null;
  if($this->temporary && is_file($this->temporary)){unlink($this->temporary);$this->temporary=null;}
 }
}
