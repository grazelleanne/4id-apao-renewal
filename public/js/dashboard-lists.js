/* Keep list rows in one scrolling table so headers and cells stay aligned. */
(() => {
  'use strict';
  const main = document.querySelector('.app-dashboard main');
  if (!main) return;
  let queued = false;
  function refresh() {
    queued = false;
    const tables = [...main.querySelectorAll('table')].filter(table =>
      table.querySelector('thead') && table.querySelector('tbody[id]') &&
      !table.closest('[role="dialog"], .modal-box, .modal-bg, .rpcsp-preview-section, .rpcsp-paper, .ics-paper, #viewChecklist, #page-registration')
    );
    for (const table of tables) {
      let scroller = table.parentElement;
      if (!scroller.classList.contains('list-scroll-region')) {
        if (!scroller.classList.contains('overflow-x-auto')) {
          scroller = document.createElement('div');
          table.before(scroller);
          scroller.append(table);
        }
        scroller.classList.add('list-scroll-region');
        scroller.tabIndex = 0;
        scroller.setAttribute('role', 'region');
        scroller.setAttribute('aria-label', 'Scrollable records');
        table.classList.add('aligned-list-table');
      }
      if (!table.getClientRects().length) continue;
      // Reserve space for the card's record count and pagination below the table.
      let footer = 8;
      for (let container = scroller; container && container !== main; container = container.parentElement) {
        for (let next = container.nextElementSibling; next; next = next.nextElementSibling) {
          if (next.getClientRects().length && !next.matches('script, style, [role="dialog"], .modal-bg')) {
            const nextStyle = getComputedStyle(next);
            footer += next.getBoundingClientRect().height + (parseFloat(nextStyle.marginTop) || 0) + (parseFloat(nextStyle.marginBottom) || 0);
          }
        }
        const parentStyle = getComputedStyle(container.parentElement);
        footer += (parseFloat(parentStyle.paddingBottom) || 0);
        const ownStyle = getComputedStyle(container);
        footer += (parseFloat(ownStyle.marginBottom) || 0);
      }
      const top = scroller.getBoundingClientRect().top;
      const available = main.getBoundingClientRect().bottom - Math.max(top, 100) - footer;
      const height = Math.max(180, available);
      const value = `${Math.round(height)}px`;
      if (scroller.style.maxHeight !== value) scroller.style.maxHeight = value;
    }
  }
  function schedule() {
    if (!queued) { queued = true; requestAnimationFrame(refresh); }
  }
  new MutationObserver(schedule).observe(main, {childList:true, subtree:true, attributes:true, attributeFilter:['class','style','hidden']});
  window.addEventListener('resize', schedule);
  main.addEventListener('scroll', schedule, {passive:true});
  if (document.fonts) document.fonts.ready.then(schedule);
  schedule();
})();
