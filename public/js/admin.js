/* ============================================================
   KB 'Aisyiyah 2 Dau — admin.js
   Glue between the admin dashboard shell and the /admin/* JSON API:
   panel navigation, per-panel "Simpan Perubahan" for single-instance
   content, image uploads, the messages inbox, and a single generic
   CollectionManager that drives every collection's list + create/edit
   form purely from the data-fields schema config/ecms.php renders
   into the page (see components/admin/collection-manager.blade.php).
   Adding a new collection needs no changes here.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  /* ---------- toast ---------- */
  var toastEl = document.getElementById('dashToast');
  var toastTimer;
  function toast(message, isError) {
    if (!toastEl) return;
    toastEl.textContent = message;
    toastEl.classList.toggle('bg-red-800', !!isError);
    toastEl.classList.toggle('bg-forest-800', !isError);
    toastEl.classList.remove('translate-y-24', 'opacity-0');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      toastEl.classList.add('translate-y-24', 'opacity-0');
    }, 3200);
  }

  /* ---------- fetch helper: JSON in, JSON out, {success,message,data,errors} ---------- */
  async function api(url, options) {
    options = options || {};
    options.headers = Object.assign({
      'X-CSRF-TOKEN': CSRF,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
    }, options.headers || {});

    var res = await fetch(url, options);
    var data = null;
    try { data = await res.json(); } catch (e) { /* no body */ }

    if (!res.ok) {
      var message = (data && data.message) || 'Terjadi kesalahan (' + res.status + ').';
      if (data && data.errors) {
        message = Object.values(data.errors).flat().join(' ');
      }
      // 413: PHP refused the request body outright (post_max_size), so
      // Laravel never reached our validation and its message is in English.
      if (res.status === 413) {
        message = 'File terlalu besar untuk diunggah. Silakan perkecil ukurannya terlebih dahulu.';
      }
      throw new Error(message);
    }

    return data || {};
  }

  function jsonBody(payload) {
    return { headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) };
  }

  function escapeHtml(str) {
    return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ---------- 1. Dashboard sidebar panel navigation ---------- */
  var dashLinks = document.querySelectorAll('.dash-nav-link');
  var dashPanels = document.querySelectorAll('.dash-panel');
  var dashTitle = document.getElementById('dashPanelTitle');

  function activatePanel(target) {
    dashLinks.forEach(function (l) {
      var match = l.getAttribute('data-panel') === target;
      l.classList.toggle('bg-sand-50/10', match);
      l.classList.toggle('text-sand-50', match);
      l.classList.toggle('text-sand-100/70', !match);
    });
    dashPanels.forEach(function (panel) {
      var match = panel.getAttribute('data-panel') === target;
      panel.classList.toggle('hidden', !match);
      if (match) {
        panel.querySelectorAll('[data-collection-manager]').forEach(function (el) {
          var manager = managers.get(el);
          if (manager) manager.load();
        });
      }
    });
    if (dashTitle) {
      var link = document.querySelector('.dash-nav-link[data-panel="' + target + '"]');
      dashTitle.textContent = link ? link.textContent.trim() : '';
    }
    var mobileSidebar = document.getElementById('dashSidebar');
    if (mobileSidebar && window.innerWidth < 1024) mobileSidebar.classList.add('-translate-x-full');
  }

  dashLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      activatePanel(link.getAttribute('data-panel'));
    });
  });

  var dashMenuBtn = document.getElementById('dashMenuBtn');
  var dashSidebar = document.getElementById('dashSidebar');
  if (dashMenuBtn && dashSidebar) {
    dashMenuBtn.addEventListener('click', function () {
      dashSidebar.classList.toggle('-translate-x-full');
    });
  }

  /* ---------- 2. Single-instance content: "Simpan Perubahan" per panel ---------- */
  document.querySelectorAll('[data-save-panel]').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      // A panel can hold more than one card (e.g. About Us separates
      // "Visi & Misi" from the rest); a card marked [data-save-scope]
      // saves only its own fields, otherwise the button covers the panel.
      var scope = btn.closest('[data-save-scope]') || btn.closest('[data-panel]');
      var fields = scope.querySelectorAll('[data-ecms-content]');
      btn.disabled = true;
      try {
        await Promise.all(Array.from(fields).map(function (field) {
          return api('/admin/content/' + field.dataset.section + '/' + field.dataset.key, Object.assign(
            { method: 'PATCH' },
            jsonBody({ value: field.value })
          ));
        }));
        toast('Perubahan tersimpan.');
      } catch (e) {
        toast(e.message, true);
      } finally {
        btn.disabled = false;
      }
    });
  });

  /* ---------- 3. Single-instance content images (upload immediately) ---------- */
  document.querySelectorAll('[data-ecms-image]').forEach(function (input) {
    input.addEventListener('change', async function () {
      if (!input.files[0]) return;
      var fd = new FormData();
      fd.append('image', input.files[0]);
      fd.append('_method', 'PATCH');
      try {
        var res = await api('/admin/content/' + input.dataset.section + '/' + input.dataset.key, { method: 'POST', body: fd });
        var preview = input.closest('[data-ecms-image-field]')?.querySelector('[data-ecms-image-preview]');
        if (preview && res.data && res.data.value) {
          preview.innerHTML = '<img src="/storage/' + res.data.value + '" class="w-full h-full object-cover" alt="">';
        }
        toast('Gambar diperbarui.');
      } catch (e) {
        toast(e.message, true);
      }
    });
  });

  document.querySelectorAll('[data-ecms-image-clear]').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      if (!confirm('Hapus gambar ini?')) return;
      try {
        await api('/admin/content/' + btn.dataset.section + '/' + btn.dataset.key, Object.assign(
          { method: 'PATCH' },
          jsonBody({ clear: true })
        ));
        toast('Gambar dihapus.');
        var preview = btn.closest('[data-ecms-image-field]')?.querySelector('[data-ecms-image-preview]');
        if (preview) preview.innerHTML = '';
      } catch (e) {
        toast(e.message, true);
      }
    });
  });

  /* ---------- 4. Site settings forms (site_settings row, can be split
     across multiple panels/forms — e.g. "Kelola Contact" and "Pengaturan
     Situs" — each submitting only the fields it owns) ---------- */
  document.querySelectorAll('[data-settings-form]').forEach(function (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      var payload = Object.fromEntries(new FormData(form).entries());
      try {
        await api('/admin/settings', Object.assign({ method: 'PUT' }, jsonBody(payload)));
        toast('Pengaturan tersimpan.');
      } catch (e2) {
        toast(e2.message, true);
      }
    });
  });

  /* ---------- 5. Pesan Masuk (contact messages) ---------- */
  document.querySelectorAll('[data-message-read]').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      try {
        await api('/admin/messages/' + btn.dataset.id + '/read', { method: 'PATCH' });
        btn.closest('[data-message-row]')?.querySelector('[data-badge-new]')?.remove();
        btn.remove();
        toast('Pesan ditandai sudah dibaca.');
      } catch (e) {
        toast(e.message, true);
      }
    });
  });

  document.querySelectorAll('[data-message-delete]').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      if (!confirm('Hapus pesan ini?')) return;
      try {
        await api('/admin/messages/' + btn.dataset.id, { method: 'DELETE' });
        btn.closest('[data-message-row]')?.remove();
        toast('Pesan dihapus.');
      } catch (e) {
        toast(e.message, true);
      }
    });
  });

  /* ---------- 6. Generic collection manager ---------- */
  function CollectionManager(el) {
    this.el = el;
    this.resource = el.dataset.resource;
    this.labelField = el.dataset.labelField;
    this.hasStatus = el.dataset.hasStatus === '1';
    this.hasSort = el.dataset.hasSort === '1';
    this.imageField = el.dataset.imageField || null;
    this.imageHint = el.dataset.imageHint || '';
    this.fields = JSON.parse(el.dataset.fields || '[]');
    this.listEl = el.querySelector('[data-list]');
    this.formWrap = el.querySelector('[data-form-wrap]');
    this.fieldsContainer = el.querySelector('[data-fields-container]');
    this.items = [];
    this.editingId = null;
    this.loaded = false;

    var self = this;
    el.querySelector('[data-action="create"]').addEventListener('click', function () { self.openForm(null); });
  }

  CollectionManager.prototype.load = async function () {
    if (this.loaded) return;
    this.loaded = true;
    try {
      var res = await api('/admin/' + this.resource);
      this.items = res.data || [];
      this.render();
    } catch (e) {
      toast(e.message, true);
    }
  };

  CollectionManager.prototype.render = function () {
    var self = this;
    this.listEl.innerHTML = '';
    if (!this.items.length) {
      this.listEl.innerHTML = '<p class="text-sm text-ink/40">Belum ada data.</p>';
      return;
    }
    this.items.forEach(function (item, index) {
      self.listEl.appendChild(self.renderRow(item, index));
    });
  };

  CollectionManager.prototype.renderRow = function (item, index) {
    var self = this;
    var row = document.createElement('div');
    row.className = 'border border-sand-300 bg-white p-4 flex items-center gap-4';

    var thumb = '';
    if (this.imageField) {
      var src = item[this.imageField];
      thumb = src
        ? '<img src="/storage/' + src + '" class="w-12 h-12 object-cover border border-sand-300 shrink-0" alt="">'
        : '<div class="w-12 h-12 border border-sand-300 bg-sand-50 shrink-0"></div>';
    }

    var statusBadge = this.hasStatus
      ? '<button type="button" data-action="status" class="text-[10px] font-semibold uppercase px-2 py-1 shrink-0 ' +
        (item.status === 'published' ? 'bg-forest-700 text-sand-50' : 'border border-sand-400 text-ink/50') + '">' +
        (item.status === 'published' ? 'Terbit' : 'Draft') + '</button>'
      : '';

    var sortBtns = this.hasSort
      ? '<div class="flex flex-col shrink-0 leading-none">' +
        '<button type="button" data-action="up" class="text-ink/40 hover:text-forest-700 disabled:opacity-20"' + (index === 0 ? ' disabled' : '') + '>&#9650;</button>' +
        '<button type="button" data-action="down" class="text-ink/40 hover:text-forest-700 disabled:opacity-20"' + (index === this.items.length - 1 ? ' disabled' : '') + '>&#9660;</button>' +
        '</div>'
      : '';

    row.innerHTML =
      sortBtns + thumb +
      '<p class="flex-1 text-sm font-semibold text-forest-800 truncate">' + escapeHtml(item[this.labelField]) + '</p>' +
      statusBadge +
      '<button type="button" data-action="edit" class="text-[11px] font-semibold uppercase tracking-wide text-forest-700 hover:text-forest-500 shrink-0">Ubah</button>' +
      '<button type="button" data-action="delete" class="text-[11px] font-semibold uppercase tracking-wide text-ink/40 hover:text-red-700 shrink-0">Hapus</button>';

    row.querySelector('[data-action="edit"]').addEventListener('click', function () { self.openForm(item); });
    row.querySelector('[data-action="delete"]').addEventListener('click', function () { self.remove(item); });
    if (this.hasStatus) row.querySelector('[data-action="status"]').addEventListener('click', function () { self.toggleStatus(item); });
    if (this.hasSort) {
      var up = row.querySelector('[data-action="up"]');
      var down = row.querySelector('[data-action="down"]');
      if (up) up.addEventListener('click', function () { self.move(index, -1); });
      if (down) down.addEventListener('click', function () { self.move(index, 1); });
    }

    return row;
  };

  CollectionManager.prototype.openForm = function (item) {
    var self = this;
    this.editingId = item ? item.id : null;
    this.fieldsContainer.innerHTML = '';
    this.fields.forEach(function (field) {
      self.fieldsContainer.appendChild(self.renderField(field, item));
    });

    var actions = document.createElement('div');
    actions.className = 'flex items-center gap-3 pt-2';
    actions.innerHTML =
      '<button type="button" data-action="submit" class="bg-forest-700 hover:bg-forest-800 text-sand-50 text-xs font-semibold uppercase tracking-wide px-5 py-2.5">' +
      (item ? 'Simpan' : 'Tambah') + '</button>' +
      '<button type="button" data-action="cancel" class="text-xs font-semibold uppercase tracking-wide text-ink/50">Batal</button>';
    this.fieldsContainer.appendChild(actions);
    actions.querySelector('[data-action="submit"]').addEventListener('click', function () { self.submit(); });
    actions.querySelector('[data-action="cancel"]').addEventListener('click', function () { self.closeForm(); });

    this.formWrap.classList.remove('hidden');
    this.formWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  CollectionManager.prototype.renderField = function (field, item) {
    if (field.type === 'repeater') {
      return this.renderRepeaterField(field, item);
    }

    var value = item ? item[field.name] : '';
    var id = 'f_' + this.resource + '_' + field.name;
    var control = '';

    if (field.type === 'textarea' || field.type === 'richtext') {
      control = '<textarea id="' + id + '" rows="' + (field.type === 'richtext' ? 6 : 3) +
        '" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">' + escapeHtml(value) + '</textarea>';
    } else if (field.type === 'boolean') {
      control = '<label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" id="' + id + '"' +
        (value ? ' checked' : '') + ' class="w-4 h-4 accent-forest-700"> Ya</label>';
    } else if (field.type === 'date') {
      control = '<input type="date" id="' + id + '" value="' + escapeHtml(value) + '" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">';
    } else if (field.type === 'select') {
      var opts = Object.entries(field.options || {}).map(function ([k, v]) {
        return '<option value="' + k + '"' + (String(value) === String(k) ? ' selected' : '') + '>' + escapeHtml(v) + '</option>';
      }).join('');
      control = '<select id="' + id + '" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm"><option value="">&mdash;</option>' + opts + '</select>';
    } else if (field.type === 'image') {
      // A file input can't be pre-filled, so show the image already on the
      // record — otherwise an empty input reads as "no image set".
      var current = item && this.imageField ? item[this.imageField] : null;
      control =
        (current
          ? '<div class="flex items-center gap-3 mb-2">' +
            '<img src="/storage/' + current + '" class="w-16 h-16 object-cover border border-sand-300" alt="">' +
            '<span class="text-[11px] text-ink/50">Gambar saat ini — pilih file baru untuk mengganti.</span>' +
            '</div>'
          : '') +
        '<input type="file" id="' + id + '" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-xs">' +
        (this.imageHint ? '<p class="text-[11px] text-ink/50 mt-2">' + escapeHtml(this.imageHint) + '</p>' : '');
    } else {
      control = '<input type="text" id="' + id + '" value="' + escapeHtml(value) + '" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">';
    }

    var wrap = document.createElement('div');
    wrap.innerHTML = '<label for="' + id + '" class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">' + field.label + '</label>' + control;
    wrap.dataset.fieldName = field.name;
    wrap.dataset.fieldType = field.type;
    return wrap;
  };

  /**
   * A 'repeater' field is edited inline as part of the parent's own form
   * (e.g. a staff member's education history) instead of as a separate
   * top-level collection — see config/ecms.php's field shape docs and
   * CollectionService::syncRepeaters(), which the whole row set is sent to
   * on save (bracket-notation FormData keys: name[i][childName]).
   */
  CollectionManager.prototype.renderRepeaterField = function (field, item) {
    var self = this;
    var wrap = document.createElement('div');
    wrap.dataset.fieldName = field.name;
    wrap.dataset.fieldType = 'repeater';
    wrap.className = 'border border-sand-200 bg-sand-50/60 p-4';

    var header = document.createElement('div');
    header.className = 'flex items-center justify-between mb-3';
    header.innerHTML =
      '<label class="block text-xs font-semibold uppercase tracking-wide text-forest-800">' + field.label + '</label>' +
      '<button type="button" data-action="add-row" class="text-[11px] font-semibold uppercase tracking-wide text-forest-700 hover:text-forest-500">+ Tambah</button>';
    wrap.appendChild(header);

    var rowsContainer = document.createElement('div');
    rowsContainer.className = 'space-y-3';
    wrap.appendChild(rowsContainer);

    var existingRows = (item && item[field.name]) || [];
    if (existingRows.length) {
      existingRows.forEach(function (rowData) {
        rowsContainer.appendChild(self.renderRepeaterRow(field, rowData));
      });
    } else {
      rowsContainer.innerHTML = '<p class="text-xs text-ink/40" data-repeater-empty>Belum ada data. Klik &quot;+ Tambah&quot; untuk menambahkan.</p>';
    }

    header.querySelector('[data-action="add-row"]').addEventListener('click', function () {
      rowsContainer.querySelector('[data-repeater-empty]')?.remove();
      rowsContainer.appendChild(self.renderRepeaterRow(field, null));
    });

    return wrap;
  };

  CollectionManager.prototype.renderRepeaterRow = function (field, rowData) {
    var row = document.createElement('div');
    row.dataset.repeaterRow = '1';
    row.className = 'border border-sand-300 bg-white p-3 grid sm:grid-cols-3 gap-3 items-end relative';

    field.child_fields.forEach(function (child) {
      var val = rowData ? (rowData[child.name] || '') : '';
      var fieldWrap = document.createElement('div');
      fieldWrap.innerHTML =
        '<label class="block text-[10px] font-semibold uppercase tracking-wide text-ink/50 mb-1">' + child.label + '</label>' +
        '<input type="text" data-child-name="' + child.name + '" value="' + escapeHtml(val) + '" class="w-full border border-sand-300 bg-sand-50 px-3 py-2 text-sm">';
      row.appendChild(fieldWrap);
    });

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.title = 'Hapus baris ini';
    removeBtn.className = 'absolute -top-2 -right-2 w-5 h-5 rounded-full bg-sand-400 hover:bg-red-600 text-white text-xs leading-none flex items-center justify-center';
    removeBtn.innerHTML = '&times;';
    removeBtn.addEventListener('click', function () { row.remove(); });
    row.appendChild(removeBtn);

    return row;
  };

  CollectionManager.prototype.closeForm = function () {
    this.formWrap.classList.add('hidden');
    this.fieldsContainer.innerHTML = '';
    this.editingId = null;
  };

  CollectionManager.prototype.submit = async function () {
    var fd = new FormData();
    this.fieldsContainer.querySelectorAll('[data-field-name]').forEach(function (wrap) {
      var name = wrap.dataset.fieldName;
      var type = wrap.dataset.fieldType;

      if (type === 'repeater') {
        wrap.querySelectorAll('[data-repeater-row]').forEach(function (row, i) {
          row.querySelectorAll('[data-child-name]').forEach(function (input) {
            fd.append(name + '[' + i + '][' + input.dataset.childName + ']', input.value);
          });
        });
        return;
      }

      var input = wrap.querySelector('input, textarea, select');
      if (type === 'image') {
        if (input.files[0]) fd.append('image', input.files[0]);
      } else if (type === 'boolean') {
        fd.append(name, input.checked ? '1' : '0');
      } else {
        fd.append(name, input.value);
      }
    });

    var url = this.editingId ? '/admin/' + this.resource + '/' + this.editingId : '/admin/' + this.resource;
    fd.append('_method', this.editingId ? 'PUT' : 'POST');

    try {
      await api(url, { method: 'POST', body: fd });
      toast(this.editingId ? 'Item diperbarui.' : 'Item ditambahkan.');
      this.closeForm();
      this.loaded = false;
      await this.load();
    } catch (e) {
      toast(e.message, true);
    }
  };

  CollectionManager.prototype.remove = async function (item) {
    if (!confirm('Hapus item ini?')) return;
    try {
      await api('/admin/' + this.resource + '/' + item.id, { method: 'DELETE' });
      toast('Item dihapus.');
      this.loaded = false;
      await this.load();
    } catch (e) {
      toast(e.message, true);
    }
  };

  CollectionManager.prototype.toggleStatus = async function (item) {
    var next = item.status === 'published' ? 'draft' : 'published';
    try {
      await api('/admin/' + this.resource + '/' + item.id + '/status', Object.assign({ method: 'PATCH' }, jsonBody({ status: next })));
      item.status = next;
      this.render();
    } catch (e) {
      toast(e.message, true);
    }
  };

  CollectionManager.prototype.move = async function (index, delta) {
    var target = index + delta;
    if (target < 0 || target >= this.items.length) return;
    var items = this.items.slice();
    var moved = items.splice(index, 1)[0];
    items.splice(target, 0, moved);
    this.items = items;
    this.render();
    try {
      await api('/admin/' + this.resource + '/reorder', Object.assign({ method: 'POST' }, jsonBody({ ids: items.map(function (i) { return i.id; }) })));
    } catch (e) {
      toast(e.message, true);
    }
  };

  var managers = new Map();
  document.querySelectorAll('[data-collection-manager]').forEach(function (el) {
    managers.set(el, new CollectionManager(el));
  });

  // The overview panel is visible by default on load; make sure any
  // manager already inside a visible panel loads immediately too.
  document.querySelectorAll('.dash-panel:not(.hidden) [data-collection-manager]').forEach(function (el) {
    var manager = managers.get(el);
    if (manager) manager.load();
  });

});
