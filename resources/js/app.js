import './bootstrap';
import * as bootstrap from 'bootstrap';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

window.bootstrap = bootstrap;
window.L = L;

/* Theme: persisted, no flash because <head> applies the attribute early. */
const root = document.documentElement;
const applyTheme = (theme) => {
  root.dataset.theme = theme;
  localStorage.setItem('zt-theme', theme);
  document.dispatchEvent(new CustomEvent('zt:theme', { detail: theme }));
};

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
    });
  });

  /* Sidebar (mobile) */
  const sidebar = document.querySelector('.sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');
  const toggles = document.querySelectorAll('[data-sidebar-toggle]');
  const setNav = (open) => {
    sidebar?.classList.toggle('open', open);
    backdrop?.classList.toggle('show', open);
    document.body.classList.toggle('nav-open', open);
    toggles.forEach((b) => b.setAttribute('aria-expanded', open ? 'true' : 'false'));
  };
  toggles.forEach((b) => b.addEventListener('click', () => setNav(!sidebar?.classList.contains('open'))));
  backdrop?.addEventListener('click', () => setNav(false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setNav(false); });
  sidebar?.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setNav(false)));

  /* Public nav: shadow + mobile panel */
  const nav = document.querySelector('.pub-nav');
  if (nav) {
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }
  const burger = document.querySelector('[data-pub-menu]');
  const panel = document.querySelector('.pub-mobile');
  burger?.addEventListener('click', () => {
    const open = panel?.classList.toggle('open');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  /* Mini maps: .map-mini with data-lat/data-lng */
  document.querySelectorAll('.map-mini').forEach((el) => {
    if (!window.L) return;
    const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
    if (Number.isNaN(lat) || Number.isNaN(lng)) return;
    const map = L.map(el, { zoomControl: false, attributionControl: false }).setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(el.dataset.label || 'Posisi');
    setTimeout(() => map.invalidateSize(), 200);
  });

  /* Flash auto-dismiss */
  document.querySelectorAll('.alert-auto').forEach((el) => {
    setTimeout(() => { el.style.transition = 'opacity .4s, transform .4s'; el.style.opacity = '0'; el.style.transform = 'translateY(-4px)'; }, 4500);
    setTimeout(() => el.remove(), 5000);
  });

  /* Destructive actions */
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('submit', (e) => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
    if (el.tagName === 'BUTTON' || el.tagName === 'A') {
      el.addEventListener('click', (e) => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
      });
    }
  });

  /* Enter-to-submit for filter toolbars */
  document.querySelectorAll('.toolbar input[type="search"]').forEach((input) => {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); input.form?.submit(); }
    });
  });

  /* Animate progress bars into view */
  document.querySelectorAll('.bar-list__fill').forEach((el) => {
    const w = el.style.width;
    el.style.width = '0';
    requestAnimationFrame(() => requestAnimationFrame(() => { el.style.transition = 'width .9s cubic-bezier(.16,1,.3,1)'; el.style.width = w; }));
  });
});
