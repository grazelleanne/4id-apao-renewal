// DFDs describe data movement, rather than workflow decisions or FK cardinalities.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const esc = value => String(value).replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[char]));
const theme={theme:'base',themeVariables:{background:'#000000',primaryColor:'#000000',primaryTextColor:'#ffffff',primaryBorderColor:'#ffffff',lineColor:'#ffffff',textColor:'#ffffff',edgeLabelBackground:'#000000'},flowchart:{nodeSpacing:100,rankSpacing:160,htmlLabels:false,curve:'stepAfter'}};
const processes=[
  ['1.0 Authenticate and manage accounts','Staff / Administrator','Credentials, new password or account changes','Authentication result and account details',[
    ['users','Account and password updates','Account credentials and permissions'],
  ]],
  ['2.0 Manage personnel','Staff / Administrator','Personnel and firearm details; archive or restore request','Personnel records and save result',[
    ['personnel','New or updated personnel records','Personnel and firearm records'],
  ]],
  ['3.0 Inspect and record renewal','Staff / Administrator','Inspection request, findings and approval details','Inspection details and renewal status',[
    ['inspections','Inspection request and findings','Inspection records'],
    ['personnel','Validity and approval updates','Personnel and firearm details'],
    ['renewal_history','Renewal history entries','Renewal history'],
  ]],
  ['4.0 Manage property receipts','Staff / Administrator','Receipt, equipment and signature details','Receipt records and printable receipt',[
    ['property_acknowledgement_receipts','Receipt and replacement records','Receipt records'],
    ['personnel',null,'Recipient and issuing officer details'],
  ]],
  ['5.0 Manage notifications','Staff / Administrator','Notification request or read acknowledgement','Notification messages and unread counts',[
    ['notifications','Notification messages and read flags','Notification records'],
    ['personnel',null,'Personnel name and contact details'],
  ]],
  ['6.0 Generate reports','Staff / Administrator','Report filters or export request','Personnel reports, RPCSP, inspection PDFs',[
    ['personnel',null,'Personnel, firearm and validity records'],
    ['inspections',null,'Inspection findings and signatures'],
    ['ics_settings',null,'Office and document settings'],
  ]],
  ['7.0 Record and review audit activity','Administrator / System processes','Audit filters or system action details','Filtered audit records and exports',[
    ['audit_logs','System activity entries','Audit records'],
  ]],
];
function render(name,id,nodes,edges) {
  const cells=['<mxCell id="0"/><mxCell id="1" parent="0"/>'];
  const mermaid=['%%{init: '+JSON.stringify(theme)+'}%%','flowchart LR'];
  const nodeIds=new Set(nodes.map(n=>n.id));
  for(const n of nodes) {
    const shape=n.kind==='process'?'ellipse;':n.kind==='store'?'rounded=0;spacingLeft=56;':'rounded=1;arcSize=25;';
    const width=n.kind==='process'?240:360;
    const height=n.kind==='process'?240:n.kind==='store'?90:120;
    const x=n.x+(n.kind==='process'?60:0);
    cells.push(`<mxCell id="${n.id}" value="${esc(n.label)}" style="${shape}html=0;whiteSpace=wrap;fillColor=#000000;strokeColor=#ffffff;fontColor=#ffffff;fontSize=18;spacing=16;align=center;verticalAlign=middle;" vertex="1" parent="1"><mxGeometry x="${x}" y="${n.y}" width="${width}" height="${height}" as="geometry"/></mxCell>`);
    if(n.kind==='store') cells.push(`<mxCell id="${n.id}-division" value="" style="rounded=0;html=0;fillColor=#000000;strokeColor=#ffffff;" vertex="1" connectable="0" parent="${n.id}"><mxGeometry x="0" y="0" width="44" height="${height}" as="geometry"/></mxCell>`);
    mermaid.push(`    ${n.id}${n.kind==='process'?'((' :n.kind==='store'?'[[':'(['}"${n.label}"${n.kind==='process'?'))':n.kind==='store'?']]':'])'}`);
  }
  edges.forEach(([a,b,label],i)=>{
    assert(nodeIds.has(a)&&nodeIds.has(b));
    // Parallel flows use distinct top/bottom attachment points and labels.
    const source=nodes.find(n=>n.id===a),target=nodes.find(n=>n.id===b);
    const forward=source.x<target.x;
    const fraction=forward?0.25:0.75;
    cells.push(`<mxCell id="edge-${i}" value="${esc(label)}" style="edgeStyle=orthogonalEdgeStyle;rounded=0;html=0;exitX=${forward?1:0};exitY=${fraction};entryX=${forward?0:1};entryY=${fraction};exitPerimeter=0;entryPerimeter=0;endArrow=block;endFill=1;strokeColor=#ffffff;fontColor=#ffffff;fontSize=15;labelBackgroundColor=#000000;jettySize=40;" edge="1" parent="1" source="${a}" target="${b}"><mxGeometry relative="1" as="geometry"><mxPoint x="0" y="${forward?-20:20}" as="offset"/></mxGeometry></mxCell>`);
    mermaid.push(`    ${a} -->|"${label}"| ${b}`);
  });
  mermaid.push('    classDef default fill:#000000,stroke:#ffffff,color:#ffffff;','    linkStyle default stroke:#ffffff,color:#ffffff;');
  fs.writeFileSync(path.join(__dirname,`project-dfd-${id}.mmd`),mermaid.join('\n')+'\n');
  return `<diagram id="${id}" name="${esc(name)}"><mxGraphModel background="#000000" grid="1" gridSize="10" page="0"><root>${cells.join('\n')}</root></mxGraphModel></diagram>`;
}
const pages=[];
pages.push(render('Context - whole system','context',[
  {id:'staff',label:'Staff',kind:'entity',x:60,y:160},
  {id:'system',label:'0 - APAO Renewal System',kind:'process',x:920,y:330},
  {id:'admin',label:'Administrator',kind:'entity',x:1780,y:500},
],[
  ['staff','system','Credentials, personnel, inspections, receipts, report requests'],
  ['system','staff','Access result, personnel records, notifications, reports'],
  ['admin','system','Credentials, account changes, approvals, filters'],
  ['system','admin','Account and personnel records, inspection results, reports, audit logs'],
]));
const overviewNodes=[],overviewEdges=[];
processes.forEach(([label,actor,input,output,stores],index)=>{
  const y=140+index*840;
  const actorId=`actor${index}`,processId=`process${index}`;
  overviewNodes.push({id:actorId,label:actor,kind:'entity',x:60,y:y+240},{id:processId,label,kind:'process',x:980,y:y+240});
  const edges=[[actorId,processId,input],[processId,actorId,output]];
  const nodes=overviewNodes.slice(-2).map(n=>({...n,y:n.y-y+100}));
  stores.forEach(([table,write,read],storeIndex)=>{
    const storeId=`store${index}_${storeIndex}`;
    const store={id:storeId,label:`D - ${table}`,kind:'store',x:1900,y:y+storeIndex*260};
    overviewNodes.push(store);
    nodes.push({...store,y:100+storeIndex*260});
    if(write) edges.push([processId,storeId,write]);
    if(read) edges.push([storeId,processId,read]);
  });
  overviewEdges.push(...edges);
  pages.push(render(`Level 1 - ${label}`,`process-${index+1}`,nodes,edges));
});
// One connected model: shared actors/stores and actual process-to-process data.
const connectedNodes = [
  {id:'staff',label:'Staff',kind:'entity',x:60,y:500},
  {id:'admin',label:'Administrator',kind:'entity',x:60,y:1350},
];
const connectedEdges = [];
const storeIds = new Map();
processes.forEach(([label,actor,input,output,stores],index)=>{
  const processId=`process${index}`;
  connectedNodes.push({id:processId,label,kind:'process',x:980,y:140+index*440});
  const actors=index===6?['admin']:['staff','admin'];
  for(const actorId of actors) {
    let request=input;
    if(index===0&&actorId==='staff') request='Credentials or new password';
    if(index===1&&actorId==='staff') request='Personnel and firearm registration details';
    if(index===2&&actorId==='staff') request='Inspection submission request';
    if(index===6) request='Audit filters and export request';
    connectedEdges.push([actorId,processId,request],[processId,actorId,output]);
  }
  for(const [table,write,read] of stores) {
    if(!storeIds.has(table)) {
      const storeId=`data${storeIds.size+1}`;
      storeIds.set(table,storeId);
      connectedNodes.push({id:storeId,label:`D${storeIds.size} - ${table}`,kind:'store',x:2300,y:140+(storeIds.size-1)*340});
    }
    const storeId=storeIds.get(table);
    if(write) connectedEdges.push([processId,storeId,write]);
    if(read) connectedEdges.push([storeId,processId,read]);
  }
});
connectedEdges.push(
  ['process1','process4','Personnel registration notification data'],
  ['process2','process4','Inspection submission and renewal notification data'],
);
for(let index=0;index<6;index++) connectedEdges.push([`process${index}`,'process6','User identity, action and target details']);
// Every node must be reachable in the undirected data-flow graph.
const visited=new Set(['staff']);
let changed=true;
while(changed) { changed=false; for(const [a,b] of connectedEdges) {
  if(visited.has(a)&&!visited.has(b)) {visited.add(b);changed=true;}
  if(visited.has(b)&&!visited.has(a)) {visited.add(a);changed=true;}
} }
assert.equal(visited.size,connectedNodes.length,'Disconnected Level 1 DFD node');
const levelOne = render('Level 1 - connected system','level-1',connectedNodes,connectedEdges);
pages.splice(1,0,levelOne);
fs.writeFileSync(path.join(__dirname,'project-dfd-level-1.drawio'),`<?xml version="1.0" encoding="UTF-8"?><mxfile host="app.diagrams.net">${levelOne}</mxfile>\n`);
fs.writeFileSync(path.join(__dirname,'project-dfd.drawio'),`<?xml version="1.0" encoding="UTF-8"?><mxfile host="app.diagrams.net">${pages.join('')}</mxfile>\n`);
console.log('Generated DFD: context, Level 1 overview and 7 process pages.');
