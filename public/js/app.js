(() => {
  'use strict';
  window.Banua = {
    request(input, options = {}) {
      const url = new URL(input, location.href);
      if (url.origin !== location.origin) throw new Error('Tujuan permintaan tidak valid.');
      const headers = new Headers(options.headers || {});
      headers.set('X-Requested-With', 'XMLHttpRequest');
      headers.set('Accept', 'application/json');
      const method = (options.method || 'GET').toUpperCase();
      if (!['GET', 'HEAD'].includes(method)) {
        headers.set('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
      }
      return fetch(url.href, { ...options, headers, credentials: 'same-origin' }).then(response => {
        if (response.status === 401) throw new Error('Sesi berakhir. Silakan masuk kembali.');
        if (response.status === 403) throw new Error('Permintaan ditolak. Muat ulang halaman dan coba lagi.');
        if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) {
          throw new Error('Respons tidak valid. Muat ulang halaman dan coba lagi.');
        }
        return response;
      });
    }
  };
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    }));
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('site-navigation');
    const closeMenu = () => {
      if (!toggle || !menu) return;
      toggle.setAttribute('aria-expanded', 'false');
      menu.classList.remove('is-open');
    };
    toggle?.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') !== 'true';
      toggle.setAttribute('aria-expanded', String(open));
      menu.classList.toggle('is-open', open);
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
    document.addEventListener('click', event => { if (!event.target.closest('.site-header')) closeMenu(); });
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
      button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.textContent = visible ? 'Sembunyikan' : 'Tampilkan';
        button.setAttribute('aria-pressed', String(visible));
      });
    });
    document.querySelectorAll('form[data-prevent-double-submit]').forEach(form => {
      form.addEventListener('submit', () => {
        if (!form.checkValidity()) return;
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.textContent = 'Menyimpan…';
      });
    });
    window.addEventListener('pageshow', () => document.querySelectorAll('form[data-prevent-double-submit] button').forEach(button => {
      button.disabled = false;
      button.textContent = button.dataset.label || 'Simpan pesanan';
    }));
  });
})();
