const vm = require('vm');
const assert = require('assert');
const {spawnSync} = require('child_process');
const fixture = spawnSync('php', ['tests/report-ui-fixture.php'], {encoding:'utf8'});
assert.equal(fixture.status, 0, fixture.stderr);
const html = fixture.stdout;
const nodes = new Map();
for (const match of html.matchAll(/id="([^"]+)"/g)) nodes.set(match[1], {
  style: {}, value: '', hidden: true, innerHTML: '', innerText: '', textContent: '',
  classList: {add(){}, remove(){}, toggle(){}, contains(){return false;}},
  addEventListener(type, handler){this[type] = handler;}, contains(){return false;}, setAttribute(){},
  querySelectorAll(){return [];}, scrollIntoView(){}
});
const personnel = [{itemNumber:1,firstName:'Test',lastName:'Personnel',unit:'APAO',approvedStatus:'renewed',dateOfValidity:'2028-01-01'}];
const callbacks = [];
const context = vm.createContext({console, Date, Intl, AbortSignal, requestAnimationFrame(fn){fn();}, setInterval(){}, setTimeout,
  localStorage:{getItem(){return null;},setItem(){}},
  document:{body:nodes.get('sidebar'), getElementById(id){return nodes.get(id) ?? null;},
    querySelector(){return {content:'test'};}, querySelectorAll(){return [];},
    addEventListener(type,fn){if(type==='DOMContentLoaded') callbacks.push(fn);}},
  fetch:async()=>({ok:true,json:async()=>({success:true,data:personnel,notifications:[]})}),
  window:{}, alert(message){throw new Error(message);}, tailwind:{}
});
const rendered = html;
for (const match of rendered.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)) {
  const code = match[1];
  if (!code.includes('const ROUTES =') && !code.includes('tailwind.config')) continue;
  vm.runInContext(code, context);
}
for (const callback of callbacks) callback();
assert.match(nodes.get('personnelTableBody').innerHTML,/Personnel/, 'Server-provided personnel must render before fetch resolves.');
assert.match(nodes.get('personnelTableBody').innerHTML,/Invalid legacy byte:/);
setImmediate(()=>{
  assert.match(nodes.get('personnelTableBody').innerHTML,/Personnel/);
  nodes.get('showRpcspBtn').click();
  assert.match(nodes.get('rpcspTableBody').innerHTML,/Pistol/);
  assert.equal(nodes.get('rpcspPreviewSection').hidden, false);
  nodes.get('previewRpcspBtn').click();
  assert.equal(nodes.get('rpcspPreviewSection').hidden, true);
  nodes.get('previewRpcspBtn').click();
  assert.equal(nodes.get('rpcspPreviewSection').hidden, false);
  console.log('Report and RPCSP initialization checks passed.');
});
