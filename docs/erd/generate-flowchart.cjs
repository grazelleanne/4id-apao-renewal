// Main application workflow, based on routes in public/index.php.
const fs = require('fs');
const path = require('path');
const nodes = [
  ['start','Start','terminal',600,40],
  ['login','Enter email and password','process',600,160],
  ['valid','Active account and valid credentials?','decision',600,290],
  ['error','Show login error / temporary lock after repeated failures','process',40,290],
  ['role','Staff account?','decision',600,460],
  ['first','First login requires password change?','decision',600,640],
  ['password','Create and confirm a new strong password','process',40,640],
  ['staff','Staff dashboard','process',600,850],
  ['admin','Admin dashboard','process',1160,460],
  ['register','Register personnel and firearm details','process',600,990],
  ['submit','Submit inspection request','process',600,1130],
  ['review','Admin reviews and saves inspection','process',600,1270],
  ['approve','Mark approved / ready for renewal?','decision',600,1410],
  ['draft','Keep inspection pending for review','process',40,1410],
  ['renew','Update validity and record renewal history','process',600,1600],
  ['notify','Notify staff about renewal','process',600,1740],
  ['receipt','Manage property acknowledgement receipts','process',600,1880],
  ['report','View / export reports and inspection PDF','process',600,2020],
  ['logout','Sign out','process',600,2160],
  ['end','End','terminal',600,2300],
  ['accounts','Manage accounts / create new staff with temporary password','process',1160,700],
  ['personnel','Manage personnel / archive and restore','process',1160,920],
  ['audit','Filter audit logs and export records','process',1160,1140],
];
const links = [
  ['start','login'],['login','valid'],['valid','error','No'],['error','login','Retry'],
  ['valid','role','Yes'],['role','first','Yes'],['role','admin','No'],
  ['first','password','Yes'],['password','staff','Password updated'],['first','staff','No'],
  ['staff','register'],['register','submit'],['submit','review'],['review','approve'],
  ['approve','draft','No'],['draft','review','Review later'],['approve','renew','Yes'],
  ['renew','notify'],['notify','receipt'],['receipt','report'],['report','logout'],['logout','end'],
  ['admin','accounts'],['admin','personnel'],['admin','audit'],['admin','review','Inspection management'],
];
const escape = text => text.replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[char]));
const theme = {theme:'base',themeVariables:{background:'#000000',primaryColor:'#000000',primaryTextColor:'#ffffff',primaryBorderColor:'#ffffff',lineColor:'#ffffff',textColor:'#ffffff',edgeLabelBackground:'#000000'},flowchart:{curve:'stepAfter',nodeSpacing:120,rankSpacing:140,padding:24,htmlLabels:false}};
const mermaid = ['%%{init: '+JSON.stringify(theme)+'}%%','flowchart TB'];
for(const [id,label,shape] of nodes) mermaid.push(`    ${id}${shape==='decision'?'{':shape==='terminal'?'([':'['}"${label}"${shape==='decision'?'}':shape==='terminal'?'])':']'}`);
for(const [source,target,label] of links) mermaid.push(`    ${source} -->${label?'|"'+label+'"|':''} ${target}`);
mermaid.push('    classDef default fill:#000000,stroke:#ffffff,color:#ffffff,stroke-width:1px;','    linkStyle default stroke:#ffffff,color:#ffffff;');
fs.writeFileSync(path.join(__dirname,'project-flowchart.mmd'),mermaid.join('\n')+'\n');
const pages = [
  {name:'1 - Login',id:'login',ids:['start','login','valid','error','role','admin','first','password','staff'],links:links.slice(0,10).filter(([a,b])=>a!=='error'),positions:{start:[650,100],login:[650,360],valid:[650,620],error:[50,620],role:[650,900],admin:[1250,900],first:[650,1180],password:[50,1180],staff:[650,1720]}},
  {name:'2 - Staff workflow',id:'staff',ids:['staff','register','submit','review','notify','receipt','report','logout','end'],links:[['staff','register'],['register','submit'],['submit','review'],['review','notify','When ready'],['notify','receipt'],['receipt','report'],['report','logout'],['logout','end']]},
  {name:'3 - Admin workflow',id:'admin',ids:['admin','accounts','personnel','audit','review','approve','draft','renew','notify','report','logout'],links:[['admin','accounts'],['admin','personnel'],['admin','audit'],['admin','review'],['review','approve'],['approve','draft','No'],['approve','renew','Yes'],['renew','notify'],['notify','report'],['report','logout']],positions:{admin:[1250,100],accounts:[50,440],personnel:[650,440],audit:[1250,440],review:[1850,440],approve:[1850,740],draft:[1250,740],renew:[1850,1040],notify:[1850,1340],report:[1850,1640],logout:[1850,1940]}},
];
const diagrams=[];
for(const page of pages) {
  const cells=['<mxCell id="0"/><mxCell id="1" parent="0"/>'];
  const boxes={};
  const pageMermaid=['%%{init: '+JSON.stringify(theme)+'}%%','flowchart TB'];
  page.ids.forEach((id,index)=>{
    let [,label,shape]=nodes.find(node=>node[0]===id);
    if(page.id==='login'&&id==='admin') label='Admin dashboard (page 3)';
    if(page.id==='login'&&id==='staff') label='Staff dashboard (page 2)';
    if(id==='error') label='Login failed. Retry from login page.';
    const [x,y]=page.positions?.[id]||[80,100+index*260];
    const height=shape==='decision'?170:120;
    boxes[id]={x,y,height};
    const style=`${shape==='decision'?'rhombus;':shape==='terminal'?'rounded=1;arcSize=40;':'rounded=0;'}html=0;whiteSpace=wrap;fillColor=#000000;strokeColor=#ffffff;fontColor=#ffffff;fontSize=18;spacing=20;align=center;verticalAlign=middle;`;
    cells.push(`<mxCell id="${id}" value="${escape(label)}" style="${style}" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="440" height="${height}" as="geometry"/></mxCell>`);
    pageMermaid.push(`    ${id}${shape==='decision'?'{':shape==='terminal'?'([':'['}"${label}"${shape==='decision'?'}':shape==='terminal'?'])':']'}`);
  });
  page.links.forEach(([source,target,label=''],index)=>{
    const a=boxes[source],b=boxes[target];
    if(!a||!b) throw new Error('Missing flowchart endpoint');
    const horizontal=a.y===b.y,left=b.x<a.x;
    const ports=horizontal?`exitX=${left?0:1};exitY=0.5;entryX=${left?1:0};entryY=0.5;`:'exitX=0.5;exitY=1;entryX=0.5;entryY=0;';
    const points=a.x!==b.x&&!horizontal?[[a.x+220,a.y+a.height+80],[b.x+220,a.y+a.height+80]]:[];
    cells.push(`<mxCell id="edge-${index}" value="${escape(label)}" style="edgeStyle=orthogonalEdgeStyle;rounded=0;html=0;${ports}exitPerimeter=0;entryPerimeter=0;endArrow=block;endFill=1;strokeColor=#ffffff;fontColor=#ffffff;fontSize=16;labelBackgroundColor=#000000;spacing=8;jettySize=30;" edge="1" parent="1" source="${source}" target="${target}"><mxGeometry relative="1" as="geometry"><Array as="points">${points.map(([x,y])=>`<mxPoint x="${x}" y="${y}"/>`).join('')}</Array></mxGeometry></mxCell>`);
    pageMermaid.push(`    ${source} -->${label?'|"'+label+'"|':''} ${target}`);
  });
  const rects=Object.values(boxes);
  rects.forEach((a,i)=>rects.slice(i+1).forEach(b=>{if(!(a.x+440<=b.x||b.x+440<=a.x||a.y+a.height<=b.y||b.y+b.height<=a.y)) throw new Error('Overlapping flowchart boxes');}));
  pageMermaid.push('    classDef default fill:#000000,stroke:#ffffff,color:#ffffff;','    linkStyle default stroke:#ffffff,color:#ffffff;');
  fs.writeFileSync(path.join(__dirname,`project-flowchart-${page.id}.mmd`),pageMermaid.join('\n')+'\n');
  diagrams.push(`<diagram id="${page.id}" name="${page.name}"><mxGraphModel background="#000000" grid="1" gridSize="10" page="0"><root>${cells.join('\n')}</root></mxGraphModel></diagram>`);
}
fs.writeFileSync(path.join(__dirname,'project-flowchart.drawio'),`<?xml version="1.0" encoding="UTF-8"?><mxfile host="app.diagrams.net">${diagrams.join('')}</mxfile>\n`);
console.log('Generated 3 flowchart pages; checked box spacing and connector endpoints.');
