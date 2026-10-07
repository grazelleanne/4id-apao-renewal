// Regenerate with: node docs/erd/generate-erd.cjs
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const directory = __dirname;
const sql = fs.readFileSync(path.join(directory, '../../database/schema.sql'), 'utf8');
function splitDefinitions(text) {
  let depth = 0, quote = '', start = 0;
  const parts = [];
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    if (quote) { if (char === quote && text[i - 1] !== '\\') quote = ''; }
    else if (['"', "'", '`'].includes(char)) quote = char;
    else if (char === '(') depth++;
    else if (char === ')') depth--;
    else if (char === ',' && depth === 0) { parts.push(text.slice(start, i).trim()); start = i + 1; }
  }
  parts.push(text.slice(start).trim());
  return parts;
}
const tables = [];
const relations = [];
for (const match of sql.matchAll(/CREATE TABLE IF NOT EXISTS (\w+)\s*\(([\s\S]*?)\) ENGINE/g)) {
  const name = match[1];
  const definitions = splitDefinitions(match[2]);
  const columns = definitions.filter(line => !/^(?:KEY|UNIQUE KEY|CONSTRAINT|PRIMARY KEY)\b/i.test(line)).map(line => {
    const match = line.match(/^`?(\w+)`?\s+(\w+(?:\([^)]*\))?)/);
    assert(match, `Cannot parse ${name}: ${line}`);
    return {name:match[1], type:match[2].toLowerCase(), nullable: !/NOT NULL|PRIMARY KEY/i.test(line),
      pk:/PRIMARY KEY/i.test(line), uk:/\bUNIQUE\b/i.test(line), fk:false};
  });
  for (const line of definitions) {
    const fk = line.match(/CONSTRAINT (\w+) FOREIGN KEY\((\w+)\) REFERENCES (\w+)\((\w+)\)/);
    if (!fk) continue;
    const column = columns.find(column => column.name === fk[2]);
    assert(column, `Missing FK column ${name}.${fk[2]}`);
    column.fk = true;
    relations.push({parent:fk[3], child:name, field:fk[2], targetField:fk[4], optional:column.nullable, childOne:column.uk, enforced:true});
  }
  tables.push({name, columns});
}
assert.equal(tables.length, 11);
assert.equal(relations.length, 10);
const logical = [
  {parent:'users',child:'inspections',field:'inspected_by_user_id',targetField:'id'},
  {parent:'users',child:'renewal_transactions',field:'processed_by_user_id',targetField:'id'},
  {parent:'personnel',child:'notifications',field:'personnel_id',targetField:'id'},
  {parent:'personnel',child:'renewal_history',field:'item_number',targetField:'item_number'},
  {parent:'renewal_history',child:'renewal_transactions',field:'source_history_id',targetField:'id',childOne:true},
  {parent:'users',child:'password_resets_otp',field:'email',targetField:'email',childOne:true},
].map(relation => ({...relation,optional:true,enforced:false}));
for(const relation of [...relations,...logical]) {
  assert(tables.find(table=>table.name===relation.parent).columns.some(column=>column.name===relation.targetField));
  assert(tables.find(table=>table.name===relation.child).columns.some(column=>column.name===relation.field));
}
function mermaid(includeLogical) {
  const monochrome = {theme:'base',themeVariables:{darkMode:true,background:'#000000',primaryColor:'#000000',primaryTextColor:'#ffffff',primaryBorderColor:'#ffffff',secondaryColor:'#000000',tertiaryColor:'#000000',lineColor:'#ffffff',textColor:'#ffffff',edgeLabelBackground:'#000000',mainBkg:'#000000',entityBkg:'#000000',entityBorder:'#ffffff',entityTextColor:'#ffffff',attributeBackgroundColorOdd:'#000000',attributeBackgroundColorEven:'#000000',relationColor:'#ffffff',relationLabelColor:'#ffffff',relationLabelBackground:'#000000'}};
  // ER renderers do not consistently inherit primaryTextColor for attribute cells.
  // Explicit SVG CSS covers legacy and current Mermaid ER class names.
  monochrome.themeVariables.attributeTextColor = '#ffffff';
  monochrome.themeVariables.titleColor = '#ffffff';
  monochrome.er = { layoutDirection: 'TB', minEntityWidth: 220, entityPadding: 20, diagramPadding: 40 };
  monochrome.themeCSS = 'svg { background: #000000 !important; } text, tspan, .entityLabel, .relationshipLabel, .label { fill: #ffffff !important; color: #ffffff !important; } foreignObject div, foreignObject span, foreignObject p { color: #ffffff !important; } rect, .entityBox, .attributeBoxOdd, .attributeBoxEven, .labelBox, .relationshipLabelBox { fill: #000000 !important; stroke: #ffffff !important; } .relationshipLine, .relationship-line, .edgePath path, .flowchart-link { stroke: #ffffff !important; } marker path, marker circle, marker line { stroke: #ffffff !important; fill: #000000 !important; }';
  const lines = ['%%{init: ' + JSON.stringify(monochrome) + '}%%', '%% Generated from database/schema.sql. FK = enforced; LOGICAL = no FK constraint.',
    '%% All links are non-identifying: child primary keys do not contain the parent key.', 'erDiagram', '    direction TB'];
  for (const table of tables) {
    lines.push(`    ${table.name} {`);
    for (const column of orderedColumns(table.columns)) {
      const keys = [column.pk && 'PK', column.fk && 'FK', column.uk && 'UK'].filter(Boolean).join(',');
      const comments = [column.nullable ? 'nullable' : 'required'];
      if (logical.some(relation=>relation.child===table.name && relation.field===column.name)) comments.push('logical reference - no FK');
      if (table.name === 'personnel_data_conflicts' && ['personnel_id','field'].includes(column.name)) comments.push('joint UNIQUE personnel_id and field');
      lines.push(`        ${column.type.replace(/\(.*/, '')} ${column.name}${keys ? ' ' + keys : ''} "${comments.join('; ')}"`);
    }
    lines.push('    }');
  }
  for (const relation of [...relations,...(includeLogical ? logical : [])]) {
    lines.push(`    ${relation.parent} ${relation.optional ? '|o' : '||'}..${relation.childOne ? 'o|' : 'o{'} ${relation.child} : "${relation.enforced ? 'FK' : 'LOGICAL'} ${relation.field}"`);
  }
  return lines.join('\n') + '\n';
}
fs.writeFileSync(path.join(directory,'project-erd.mmd'), mermaid(false));
fs.writeFileSync(path.join(directory,'project-erd-logical.mmd'), mermaid(true));
const xmlEscape = text => String(text).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[char]));
const positions = {
  users:[40,170], audit_logs:[40,700], password_resets_otp:[40,1240],
  personnel:[900,170], personnel_data_conflicts:[900,1660], ics_settings:[900,2050],
  inspections:[1760,170], notifications:[1760,1950],
  renewal_transactions:[2620,170], property_acknowledgement_receipts:[2620,760], renewal_history:[3480,170]
};
const overviewFields = {
  users:['id','name','email','role','must_change_password'],
  personnel:['id','item_number','last_name','first_name','afp_serial_number','pistol_serial_number','date_of_validity','ics_status','archived_at'],
  inspections:['id','personnel_id','item_number','inspected_by_user_id','status','next_renewal_date'],
  renewal_transactions:['id','source_history_id','personnel_id','item_number','processed_by_user_id','renewal_date','new_validity_date'],
  property_acknowledgement_receipts:['id','par_number','personnel_id','previous_par_id','issued_by_personnel_id','approved_by_personnel_id','created_by','updated_by','status'],
  renewal_history:['id','item_number','action','date_of_validity'],
  notifications:['id','personnel_id','type','read_by_admin','read_by_staff'],
  password_resets_otp:['email','otp','expires_at'],audit_logs:['id','user_id','action','subject','created_at'],
  ics_settings:['id','office_name','chief_officer_name'],personnel_data_conflicts:['id','personnel_id','field','resolution_status']
};
function orderedColumns(columns) {
  const priority = column => column.pk ? 0 : column.fk ? 1 : column.uk ? 2 : 3;
  return [...columns].sort((a,b)=>priority(a)-priority(b));
}
function diagram(full) {
  const cells = ['<mxCell id="0"/><mxCell id="1" parent="0"/>'];
  function vertex(id,value,style,x,y,w,h,parent='1') {
    cells.push(`<mxCell id="${id}" value="${xmlEscape(value)}" style="${style}" vertex="1" parent="${parent}"><mxGeometry x="${x}" y="${y}" width="${w}" height="${h}" as="geometry"/></mxCell>`);
  }
  vertex('title','APAO Renewal System — '+(full?'Full physical schema':'Relationship overview'), 'text;html=0;align=left;fontColor=#ffffff;fontStyle=1;fontSize=24;',40,20,1400,40);
  vertex('legend','|| = exactly 1     o| = 0 or 1     o< = 0 to many\nSolid connectors: enforced FK. Dashed connectors: logical reference, not enforced.\n'+(full?'All columns shown.':'Key columns shown; open Full schema tab for every column.')+' No direct many-to-many or mandatory one-to-one relationship exists in this schema.', 'text;html=0;align=left;whiteSpace=wrap;fontColor=#ffffff;fontSize=14;',40,65,1550,88);
  const width = 620, rowHeight = 24;
  const boxes = {};
  const displayedColumns = table => orderedColumns(full ? table.columns : table.columns.filter(column=>overviewFields[table.name].includes(column.name)));
  let targetY = 200;
  for (const name of ['audit_logs','password_resets_otp','inspections','notifications','personnel_data_conflicts','renewal_transactions','renewal_history','property_acknowledgement_receipts']) {
    const table = tables.find(table=>table.name===name);
    const height = 34 + displayedColumns(table).length * rowHeight;
    boxes[name] = {x:1800,y:targetY,height};
    targetY += height + 200;
  }
  boxes.users = {x:40,y:200,height:34+displayedColumns(tables.find(table=>table.name==='users')).length*rowHeight};
  boxes.personnel = {x:40,y:1200,height:34+displayedColumns(tables.find(table=>table.name==='personnel')).length*rowHeight};
  boxes.ics_settings = {x:40,y:2400,height:34+displayedColumns(tables.find(table=>table.name==='ics_settings')).length*rowHeight};
  for (const table of tables) {
    const columns = displayedColumns(table);
    let [x,y] = positions[table.name];
    if (!full) {
      const compact = {users:[40,170],audit_logs:[40,490],password_resets_otp:[40,800],personnel:[900,170],personnel_data_conflicts:[900,600],ics_settings:[900,930],inspections:[1760,170],notifications:[1760,650],renewal_transactions:[2620,170],property_acknowledgement_receipts:[2620,650],renewal_history:[3480,170]};
      [x,y]=compact[table.name];
    }
    ({x,y}=boxes[table.name]);
    vertex(table.name,table.name,'swimlane;html=0;horizontal=1;startSize=34;collapsible=1;rounded=0;fillColor=#000000;swimlaneFillColor=#000000;strokeColor=#ffffff;fontColor=#ffffff;fontStyle=1;fontSize=17;',x,y,width,34+columns.length*rowHeight);
    columns.forEach((column,index)=>{
      const keys=[column.pk&&'PK',column.fk&&'FK',column.uk&&'UK'].filter(Boolean).join(',');
      vertex(`${table.name}-${column.name}-key`,keys,'text;html=0;align=center;verticalAlign=middle;strokeColor=#ffffff;fillColor=#000000;fontColor=#ffffff;fontSize=11;',0,34+index*rowHeight,68,rowHeight,table.name);
      vertex(`${table.name}-${column.name}`,column.name,'text;html=0;align=left;verticalAlign=middle;spacingLeft=9;strokeColor=#ffffff;fillColor=#000000;fontColor=#ffffff;fontSize=12;'+(column.pk?'fontStyle=4;':''),68,34+index*rowHeight,width-68,rowHeight,table.name);
    });
  }
  const links = [...relations,...(full?[]:logical)];
  links.forEach((relation,index)=>{
    const start=relation.optional?'ERzeroToOne':'ERmandOne';
    const end=relation.childOne?'ERzeroToOne':'ERzeroToMany';
    const label=`${relation.field}\n${relation.optional?'0..1':'1'} : ${relation.childOne?'0..1':'0..N'}${relation.enforced?'':' (logical)'}`;
    const parent = boxes[relation.parent], child = boxes[relation.child];
    const siblings = links.filter(link=>link.parent===relation.parent && link.child!==link.parent);
    const ordinal = siblings.indexOf(relation);
    const self = relation.parent===relation.child;
    const exitY = self ? 0.35 : (ordinal+1)/(siblings.length+1);
    const childTable = tables.find(table=>table.name===relation.child);
    const row = displayedColumns(childTable).findIndex(column=>column.name===relation.field);
    const entryY = (34+row*rowHeight+rowHeight/2)/child.height;
    const rightSide = self || parent.x===child.x;
    const lane = rightSide ? child.x+width+160+index*20 : 800+index*45;
    const sourceY = parent.y+parent.height*exitY, destinationY = child.y+child.height*entryY;
    const points = `<Array as="points"><mxPoint x="${lane}" y="${sourceY}"/><mxPoint x="${lane}" y="${destinationY}"/></Array>`;
    cells.push(`<mxCell id="relationship-${index}" value="${xmlEscape(label)}" style="edgeStyle=orthogonalEdgeStyle;rounded=0;html=0;exitX=1;exitY=${exitY};exitPerimeter=0;entryX=${rightSide?1:0};entryY=${entryY};entryPerimeter=0;jettySize=24;jumpStyle=arc;jumpSize=8;startArrow=${start};endArrow=${end};startFill=0;endFill=0;startSize=18;endSize=18;strokeColor=#ffffff;fontColor=#ffffff;strokeWidth=1.5;dashed=${relation.enforced?0:1};fontSize=11;labelBackgroundColor=#000000;" edge="1" parent="1" source="${relation.parent}" target="${relation.child}"><mxGeometry x="0.8" relative="1" as="geometry">${points}<mxPoint x="0" y="-16" as="offset"/></mxGeometry></mxCell>`);
  });
  return `<diagram id="${full?'full':'overview'}" name="${full?'Full schema':'Overview + logical references'}"><mxGraphModel background="#000000" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="0" pageScale="1" pageWidth="1169" pageHeight="827"><root>${cells.join('\n')}</root></mxGraphModel></diagram>`;
}
function cardinalityGuide() {
  const cells=['<mxCell id="0"/><mxCell id="1" parent="0"/>'];
  const examples=[
    ['Exactly one to exactly one (1 : 1)','ERmandOne','ERmandOne'],
    ['One to zero or one (1 : 0..1)','ERmandOne','ERzeroToOne'],
    ['One to zero or many (1 : 0..N)','ERmandOne','ERzeroToMany'],
    ['One to one or many (1 : 1..N)','ERmandOne','ERoneToMany'],
    ['Optional parent to zero or many (0..1 : 0..N)','ERzeroToOne','ERzeroToMany'],
    ['Many to many, optional both sides (0..N : 0..N)','ERzeroToMany','ERzeroToMany']
  ];
  cells.push('<mxCell id="guideTitle" value="Cardinality guide — notation examples only, not additional project relationships" style="text;html=0;align=left;fontColor=#ffffff;fontSize=19;fontStyle=1;" vertex="1" parent="1"><mxGeometry x="40" y="20" width="1100" height="50" as="geometry"/></mxCell>');
  examples.forEach(([label,start,end],index)=>{
    const y=100+index*120;
    for(const [name,x] of [['A',40],['B',850]]) cells.push(`<mxCell id="${name}-${index}" value="Entity ${name}" style="rounded=0;html=0;fillColor=#000000;strokeColor=#ffffff;fontColor=#ffffff;fontSize=15;" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="150" height="50" as="geometry"/></mxCell>`);
    cells.push(`<mxCell id="example-${index}" value="${xmlEscape(label)}" style="edgeStyle=orthogonalEdgeStyle;html=0;startArrow=${start};endArrow=${end};startFill=0;endFill=0;startSize=18;endSize=18;strokeColor=#ffffff;fontColor=#ffffff;labelBackgroundColor=#000000;fontSize=13;" edge="1" parent="1" source="A-${index}" target="B-${index}"><mxGeometry relative="1" as="geometry"/></mxCell>`);
  });
  return `<diagram id="guide" name="Cardinality guide"><mxGraphModel background="#000000" grid="1" gridSize="10" page="0"><root>${cells.join('\n')}</root></mxGraphModel></diagram>`;
}
fs.writeFileSync(path.join(directory,'project-erd.drawio'),`<?xml version="1.0" encoding="UTF-8"?><mxfile host="app.diagrams.net">${diagram(false)}${diagram(true)}${cardinalityGuide()}</mxfile>\n`);
fs.writeFileSync(path.join(directory,'schema-summary.json'),JSON.stringify({tables:tables.map(table=>({name:table.name,columns:table.columns.length})),enforcedRelationships:relations,logicalReferences:logical},null,2)+'\n');
require('./generate-flowchart.cjs');
console.log(`Generated ERD: ${tables.length} tables, ${tables.reduce((sum,table)=>sum+table.columns.length,0)} columns, ${relations.length} FKs, ${logical.length} logical references.`);
