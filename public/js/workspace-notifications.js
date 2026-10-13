document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.workspace-notifications');
  if (!container) return;
  const bell = container.querySelector('.workspace-bell');
  const menu = container.querySelector('.workspace-notification-menu');
  const list = container.querySelector('.workspace-notification-list');
  const badge = container.querySelector('.workspace-unread');
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  async function load() {
    try {
      const response = await fetch(container.dataset.notificationUrl, {headers: {'Accept':'application/json'}, cache:'no-store'});
      if (!response.ok) throw new Error();
      const data = await response.json();
      badge.hidden = !data.unreadCount; badge.textContent = data.unreadCount > 99 ? '99+' : data.unreadCount;
      list.replaceChildren();
      if (!data.notifications.length) {list.textContent = 'No notifications yet.'; return;}
      data.notifications.forEach(notification => {
        const item = document.createElement('button'); item.type = 'button'; item.className = 'workspace-notification-item' + (notification.read ? '' : ' unread');
        const title = document.createElement('strong'); title.textContent = notification.title;
        const message = document.createElement('span'); message.textContent = notification.message;
        item.append(title, message); list.append(item);
        item.addEventListener('click', () => markRead(notification.id));
      });
    } catch {list.textContent = 'Unable to load notifications. Click the bell to retry.';}
  }
  async function markRead(id) {
    try {
      const response = await fetch(container.dataset.readUrl, {method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(id ? {id} : {})});
      if (!response.ok) throw new Error();
      await load();
    } catch {list.textContent = 'Unable to mark notifications as read. Please try again.';}
  }
  bell.addEventListener('click', () => {menu.hidden = !menu.hidden; bell.setAttribute('aria-expanded', String(!menu.hidden)); if (!menu.hidden) load();});
  container.querySelector('.workspace-mark-read').addEventListener('click', () => markRead());
  document.addEventListener('click', event => {if (!container.contains(event.target)) {menu.hidden=true;bell.setAttribute('aria-expanded','false');}});
  document.addEventListener('keydown', event => {if(event.key==='Escape'){menu.hidden=true;bell.setAttribute('aria-expanded','false');}});
  load();
});
