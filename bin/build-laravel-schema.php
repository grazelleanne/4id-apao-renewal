<?php
declare(strict_types=1);
$root=dirname(__DIR__);
$sql=file_get_contents($root.'/database/schema.sql');
preg_match_all('/CREATE TABLE IF NOT EXISTS (\w+) \((.*?)\) ENGINE/s',$sql,$tables,PREG_SET_ORDER);
$body='';
foreach ($tables as $match) {
    [$all,$name,$columns]=$match;
    $parts=[]; $part=''; $depth=0; $quoted=false;
    foreach (str_split($columns) as $character) {
        if ($character==="'") $quoted=!$quoted;
        if (!$quoted && $character==='(') $depth++;
        if (!$quoted && $character===')') $depth--;
        if (!$quoted && !$depth && $character===',') { $parts[]=trim($part); $part=''; } else $part.=$character;
    }
    $parts[]=trim($part);
    $lines=[];
    foreach ($parts as $column) {
        if (preg_match('/^CONSTRAINT (\w+) FOREIGN KEY\((\w+)\) REFERENCES (\w+)\((\w+)\) ON DELETE (CASCADE|RESTRICT|SET NULL)/',$column,$fk)) {
            $delete=['CASCADE'=>'cascade','RESTRICT'=>'restrict','SET NULL'=>'set null'][$fk[5]];
            $lines[]="\$table->foreign('$fk[2]','$fk[1]')->references('$fk[4]')->on('$fk[3]')->onDelete('$delete');"; continue;
        }
        if (preg_match('/^(UNIQUE KEY|KEY) (\w+)\((.*?)\)/',$column,$key)) {
            $method=$key[1]==='UNIQUE KEY'?'unique':'index';
            $names="['".implode("','",array_map('trim',explode(',',$key[3])))."']";
            $lines[]="\$table->$method($names,'$key[2]');"; continue;
        }
        if (!preg_match('/^`?(\w+)`? (BIGINT|TINYINT|INT|VARCHAR|DECIMAL|LONGTEXT|TEXT|JSON|DATE|TIMESTAMP)(?:\(([^)]+)\))?(.*)$/s',$column,$field)) throw new RuntimeException('Unparsed column: '.$column);
        [, $col,$type]=$field;
        $args=$field[3]??''; $tail=$field[4]??'';
        if (str_contains($tail,'AUTO_INCREMENT')) { $lines[]="\$table->id('$col');"; continue; }
        $unsigned=str_contains($tail,'UNSIGNED');
        $method=match($type){'BIGINT'=>$unsigned?'unsignedBigInteger':'bigInteger','INT'=>$unsigned?'unsignedInteger':'integer','TINYINT'=>'boolean','VARCHAR'=>'string','DECIMAL'=>'decimal','LONGTEXT'=>'longText','TEXT'=>'text','JSON'=>'json','DATE'=>'date','TIMESTAMP'=>'timestamp'};
        $line="\$table->$method('$col'".($args!==''?",$args":'').')';
        if (!str_contains($tail,'NOT NULL') && !str_contains($tail,'PRIMARY KEY')) $line.='->nullable()';
        if (str_contains($tail,'PRIMARY KEY')) $line.='->primary()';
        if (str_contains($tail,'UNIQUE')) $line.='->unique()';
        if (preg_match('/DEFAULT (\S+)/',$tail,$default)) $line.=$default[1]==='CURRENT_TIMESTAMP'?'->useCurrent()':'->default('.$default[1].')';
        $lines[]=$line.';';
    }
    $body.="        if (!Schema::hasTable('$name')) Schema::create('$name',function(Blueprint \$table){\n            ".implode("\n            ",$lines)."\n        });\n";
}
$migration="<?php\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n$body    }\n    public function down(): void\n    {\n        throw new RuntimeException('This baseline adopts existing personnel data. Restore a backup instead of dropping shared tables.');\n    }\n};\n";
if (!is_dir($root.'/database/migrations')) mkdir($root.'/database/migrations',0777,true);
file_put_contents($root.'/database/migrations/2026_10_08_000001_adopt_apao_schema.php',$migration);
echo 'Generated Laravel migrations for '.count($tables)." existing tables.\n";
