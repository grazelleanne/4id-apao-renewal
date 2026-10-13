window.renderCompactPagination = function(container, current, pages, onGo) {
  if (!container) return;
  container.replaceChildren();
  if (pages < 1) return;
  current = Math.max(1, Math.min(current, pages));
  container.classList.add('compact-pagination');
  function arrow(label, path, target, disabled) {
    var button = document.createElement('button'); button.type = 'button';
    button.className = 'compact-page-arrow'; button.setAttribute('aria-label', label); button.title = label;
    button.disabled = disabled;
    button.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';
    button.addEventListener('click', function(){ if (!button.disabled) onGo(target); });
    container.appendChild(button);
  }
  arrow('Previous page', 'M15 18l-6-6 6-6', current-1, current===1);
  var status = document.createElement('span'); status.className='compact-page-status'; status.setAttribute('aria-live','polite');
  status.textContent='Page '+current+' of '+pages; container.appendChild(status);
  arrow('Next page', 'M9 18l6-6-6-6', current+1, current===pages);
};
