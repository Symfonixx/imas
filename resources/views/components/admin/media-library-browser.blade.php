@props([
    'instance' => 'modal',
    'autoload' => false,
])

@php
    $instance = preg_replace('/[^a-z0-9_-]/i', '', (string) $instance) ?: 'modal';
@endphp

@once
    @push('scripts')
        <script>
            (function () {
                if (window.ImasMediaLibrary) {
                    return;
                }

                var VIEW_KEY = 'imas_media_view';
                var instances = {};
                var routes = {
                    list: @json(route('admin.media_library.list')),
                    folders: @json(route('admin.media_library.folders.index')),
                    storeFolder: @json(route('admin.media_library.folders.store')),
                    destroyFolder: @json(route('admin.media_library.folders.destroy', ['folder' => '__id__'])),
                    store: @json(route('admin.media_library.store')),
                    update: @json(route('admin.media_library.update', ['media' => '__id__'])),
                    destroy: @json(route('admin.media_library.destroy', ['media' => '__id__'])),
                    deleteMulti: @json(route('admin.media_library.delete_multi')),
                };
                var i18n = {
                    root: @json(__('Root')),
                    deleteSelected: @json(__('Delete Selected')),
                    useThisImage: @json(__('Use this image')),
                    useSelectedImages: @json(__('Use selected images')),
                    selectDetails: @json(__('Select an image to see details.')),
                    noMedia: @json(__('No media found.')),
                    loading: @json(__('Loading...')),
                    name: @json(__('Name')),
                    altText: @json(__('Alt text')),
                    title: @json(__('Title')),
                    caption: @json(__('Caption')),
                    type: @json(__('Type')),
                    size: @json(__('Size')),
                    dimensions: @json(__('Dimensions')),
                    url: @json(__('URL')),
                    copy: @json(__('Copy')),
                    copyUrl: @json(__('Copy URL')),
                    uploader: @json(__('Uploader')),
                    date: @json(__('Date')),
                    folder: @json(__('Folder')),
                    saveDetails: @json(__('Save details')),
                    deleteLabel: @json(__('Delete')),
                    trashConfirm: @json(__('Move to trash / remove from library?')),
                    deleteFolderConfirm: @json(__('Delete this folder and every image inside it?')),
                    unableSave: @json(__('Unable to save image details.')),
                    unableCreateFolder: @json(__('Unable to create folder.')),
                    unableDeleteFolder: @json(__('Unable to delete folder.')),
                    uploadFailed: @json(__('Upload failed.')),
                    copied: @json(__('Copied')),
                    prev: @json(__('Prev')),
                    next: @json(__('Next')),
                };

                function csrf() {
                    var token = document.querySelector('meta[name="csrf-token"]');
                    return token ? token.getAttribute('content') : '';
                }

                function escapeHtml(value) {
                    var div = document.createElement('div');
                    div.textContent = value == null ? '' : String(value);
                    return div.innerHTML;
                }

                function escapeAttribute(value) {
                    return escapeHtml(value).replace(/"/g, '&quot;');
                }

                function formatSize(bytes) {
                    if (!bytes) return '0 B';
                    var units = ['B', 'KB', 'MB', 'GB'];
                    var i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
                }

                function getStoredView() {
                    try {
                        var value = localStorage.getItem(VIEW_KEY);
                        return value === 'list' ? 'list' : 'grid';
                    } catch (e) {
                        return 'grid';
                    }
                }

                function setStoredView(view) {
                    try {
                        localStorage.setItem(VIEW_KEY, view);
                    } catch (e) {}
                }

                function createInstance(root) {
                    var id = root.getAttribute('data-imas-ml-instance') || 'modal';
                    if (instances[id]) {
                        return instances[id];
                    }

                    var state = {
                        id: id,
                        mode: 'manage',
                        page: 1,
                        search: '',
                        type: 'all',
                        date: '',
                        sort: 'newest',
                        view: getStoredView(),
                        items: [],
                        folders: [],
                        folderId: 'root',
                        selectedId: null,
                        selectedIds: [],
                        targetInput: null,
                        targetPreview: null,
                        onSelect: null,
                        tinyEditor: null,
                        max: null,
                        searchTimer: null,
                        meta: {current_page: 1, last_page: 1},
                    };

                    function $(sel) {
                        return root.querySelector(sel);
                    }

                    function modalEl() {
                        return document.getElementById('mediaLibraryModal');
                    }

                    function isPickerMode() {
                        return state.mode === 'picker' || state.mode === 'picker-multi';
                    }

                    function currentFolderName() {
                        if (state.folderId === 'root') return i18n.root;
                        var folder = state.folders.find(function (item) {
                            return item.id === parseInt(state.folderId, 10);
                        });
                        return folder ? folder.name : i18n.root;
                    }

                    function isChecked(mediaId) {
                        return state.selectedIds.indexOf(mediaId) !== -1;
                    }

                    function getSelectedItem() {
                        if (!state.selectedId) return null;
                        return state.items.find(function (item) {
                            return item.id === state.selectedId;
                        }) || null;
                    }

                    function getOrderedSelectedItems() {
                        return state.selectedIds.map(function (mediaId) {
                            return state.items.find(function (item) {
                                return item.id === mediaId;
                            });
                        }).filter(Boolean);
                    }

                    function setBulkButtonState() {
                        var btn = $('[data-ml-bulk-delete]');
                        if (!btn) return;
                        var count = state.selectedIds.length;
                        btn.disabled = count === 0;
                        btn.textContent = i18n.deleteSelected + (count ? ' (' + count + ')' : '');
                    }

                    function updateUseButton() {
                        var wrap = $('[data-ml-use-wrap]');
                        var btn = $('[data-ml-use]');
                        if (!wrap || !btn) return;

                        if (!isPickerMode()) {
                            wrap.classList.add('d-none');
                            btn.disabled = true;
                            return;
                        }

                        wrap.classList.remove('d-none');
                        if (state.mode === 'picker-multi') {
                            btn.textContent = i18n.useSelectedImages + (state.selectedIds.length ? ' (' + state.selectedIds.length + ')' : '');
                            btn.disabled = state.selectedIds.length === 0;
                        } else {
                            btn.textContent = i18n.useThisImage;
                            btn.disabled = !getSelectedItem();
                        }
                    }

                    function updateViewToggle() {
                        root.querySelectorAll('[data-ml-view]').forEach(function (btn) {
                            var active = btn.getAttribute('data-ml-view') === state.view;
                            btn.classList.toggle('active', active);
                            btn.classList.toggle('btn-primary', active);
                            btn.classList.toggle('btn-light', !active);
                        });
                    }

                    function renderFolders() {
                        var el = $('[data-ml-folders]');
                        if (!el) return;

                        var rootActive = state.folderId === 'root' ? 'active' : '';
                        var html = '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ' + rootActive + '" data-ml-folder="root">' +
                            '<span><i class="bi bi-folder2-open me-2"></i>' + escapeHtml(i18n.root) + '</span>' +
                            '</button>';

                        html += state.folders.map(function (folder) {
                            var active = parseInt(state.folderId, 10) === folder.id ? 'active' : '';
                            return '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ' + active + '" data-ml-folder="' + folder.id + '">' +
                                '<span class="text-truncate"><i class="bi bi-folder me-2"></i>' + escapeHtml(folder.name) + '</span>' +
                                '<span class="badge badge-light-primary ms-2">' + folder.media_count + '</span>' +
                                '</button>';
                        }).join('');

                        el.innerHTML = html;

                        var current = $('[data-ml-current-folder]');
                        if (current) current.textContent = currentFolderName();

                        var deleteBtn = $('[data-ml-delete-folder]');
                        if (deleteBtn) deleteBtn.disabled = state.folderId === 'root';
                    }

                    function renderGrid(items) {
                        return items.map(function (item) {
                            var active = state.selectedId === item.id ? 'is-selected' : '';
                            var checked = isChecked(item.id) ? 'checked' : '';
                            return '' +
                                '<div class="imas-ml-grid__item ' + active + '" data-ml-item="' + item.id + '">' +
                                '  <div class="imas-ml-check" onclick="event.stopPropagation()">' +
                                '    <input type="checkbox" class="form-check-input" data-ml-check="' + item.id + '" ' + checked + '>' +
                                '  </div>' +
                                '  <div class="imas-ml-grid__thumb">' +
                                '    <img src="' + escapeAttribute(item.url) + '" alt="' + escapeAttribute(item.alt_text || '') + '" loading="lazy">' +
                                '  </div>' +
                                '  <div class="imas-ml-grid__meta text-truncate" title="' + escapeAttribute(item.name || '') + '">' + escapeHtml(item.name || '') + '</div>' +
                                '</div>';
                        }).join('');
                    }

                    function renderList(items) {
                        var rows = items.map(function (item) {
                            var active = state.selectedId === item.id ? 'table-active' : '';
                            var checked = isChecked(item.id) ? 'checked' : '';
                            return '' +
                                '<tr class="' + active + '" data-ml-item="' + item.id + '" style="cursor:pointer">' +
                                '  <td style="width:40px" class="imas-ml-check" onclick="event.stopPropagation()">' +
                                '    <input type="checkbox" class="form-check-input" data-ml-check="' + item.id + '" ' + checked + '>' +
                                '  </td>' +
                                '  <td style="width:64px">' +
                                '    <img src="' + escapeAttribute(item.url) + '" alt="" class="imas-ml-list__thumb" loading="lazy">' +
                                '  </td>' +
                                '  <td class="fw-semibold">' + escapeHtml(item.name || '-') + '</td>' +
                                '  <td class="text-muted">' + escapeHtml(item.mime_type || '-') + '</td>' +
                                '  <td class="text-muted">' + formatSize(item.size) + '</td>' +
                                '  <td class="text-muted">' + escapeHtml(item.created_at_human || item.created_at || '-') + '</td>' +
                                '</tr>';
                        }).join('');

                        return '' +
                            '<div class="table-responsive">' +
                            '  <table class="table table-row-bordered table-hover align-middle gs-3 gy-2 mb-0">' +
                            '    <thead>' +
                            '      <tr class="fw-bold text-muted">' +
                            '        <th></th><th></th>' +
                            '        <th>' + escapeHtml(i18n.name) + '</th>' +
                            '        <th>' + escapeHtml(i18n.type) + '</th>' +
                            '        <th>' + escapeHtml(i18n.size) + '</th>' +
                            '        <th>' + escapeHtml(i18n.date) + '</th>' +
                            '      </tr>' +
                            '    </thead>' +
                            '    <tbody>' + rows + '</tbody>' +
                            '  </table>' +
                            '</div>';
                    }

                    function renderItems(items) {
                        var el = $('[data-ml-items]');
                        if (!el) return;

                        state.items = items || [];
                        if (!state.items.length) {
                            el.innerHTML = '<div class="text-center text-muted py-15">' + escapeHtml(i18n.noMedia) + '</div>';
                            renderDetails(null);
                            setBulkButtonState();
                            updateUseButton();
                            return;
                        }

                        if (state.view === 'list') {
                            el.className = 'imas-ml-items imas-ml-items--list';
                            el.innerHTML = renderList(state.items);
                        } else {
                            el.className = 'imas-ml-items imas-ml-items--grid';
                            el.innerHTML = renderGrid(state.items);
                        }

                        if (!state.selectedId && state.items[0]) {
                            state.selectedId = state.items[0].id;
                        }
                        renderDetails(getSelectedItem());
                        setBulkButtonState();
                        updateUseButton();
                    }

                    function renderPagination(meta) {
                        var el = $('[data-ml-pagination]');
                        if (!el) return;
                        state.meta = meta || state.meta;
                        if ((state.meta.last_page || 1) <= 1) {
                            el.innerHTML = '';
                            return;
                        }

                        var prevDisabled = state.meta.current_page <= 1 ? 'disabled' : '';
                        var nextDisabled = state.meta.current_page >= state.meta.last_page ? 'disabled' : '';
                        el.innerHTML = '' +
                            '<button type="button" class="btn btn-sm btn-light ' + prevDisabled + '" data-ml-page="' + (state.meta.current_page - 1) + '">' + escapeHtml(i18n.prev) + '</button>' +
                            '<span class="mx-3 small text-muted">' + state.meta.current_page + ' / ' + state.meta.last_page + '</span>' +
                            '<button type="button" class="btn btn-sm btn-light ' + nextDisabled + '" data-ml-page="' + (state.meta.current_page + 1) + '">' + escapeHtml(i18n.next) + '</button>';
                    }

                    function folderOptionsHtml(selectedFolderId) {
                        var options = '<option value="">' + escapeHtml(i18n.root) + '</option>';
                        options += state.folders.map(function (folder) {
                            var selected = selectedFolderId === folder.id ? ' selected' : '';
                            return '<option value="' + folder.id + '"' + selected + '>' + escapeHtml(folder.name) + '</option>';
                        }).join('');
                        return options;
                    }

                    function renderDetails(item) {
                        var el = $('[data-ml-details]');
                        if (!el) return;

                        if (!item) {
                            el.innerHTML = '<div class="text-muted">' + escapeHtml(i18n.selectDetails) + '</div>';
                            updateUseButton();
                            return;
                        }

                        var dims = (item.width && item.height) ? (item.width + ' × ' + item.height) : '—';
                        el.innerHTML = '' +
                            '<div class="imas-ml-details__preview mb-4">' +
                            '  <img src="' + escapeAttribute(item.url) + '" alt="' + escapeAttribute(item.alt_text || '') + '">' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '  <label class="form-label">' + escapeHtml(i18n.name) + '</label>' +
                            '  <input type="text" class="form-control form-control-sm" data-ml-field="name" maxlength="255" value="' + escapeAttribute(item.name || '') + '">' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '  <label class="form-label">' + escapeHtml(i18n.altText) + '</label>' +
                            '  <input type="text" class="form-control form-control-sm" data-ml-field="alt_text" maxlength="255" value="' + escapeAttribute(item.alt_text || '') + '">' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '  <label class="form-label">' + escapeHtml(i18n.title) + '</label>' +
                            '  <input type="text" class="form-control form-control-sm" data-ml-field="title" maxlength="255" value="' + escapeAttribute(item.title || '') + '">' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '  <label class="form-label">' + escapeHtml(i18n.caption) + '</label>' +
                            '  <textarea class="form-control form-control-sm" data-ml-field="caption" rows="3" maxlength="2000">' + escapeHtml(item.caption || '') + '</textarea>' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '  <label class="form-label">' + escapeHtml(i18n.folder) + '</label>' +
                            '  <select class="form-select form-select-sm" data-ml-field="folder_id">' + folderOptionsHtml(item.folder_id) + '</select>' +
                            '</div>' +
                            '<div class="separator my-4"></div>' +
                            '<div class="small mb-2"><strong>' + escapeHtml(i18n.type) + ':</strong> ' + escapeHtml(item.mime_type || '-') + '</div>' +
                            '<div class="small mb-2"><strong>' + escapeHtml(i18n.size) + ':</strong> ' + formatSize(item.size) + '</div>' +
                            '<div class="small mb-2"><strong>' + escapeHtml(i18n.dimensions) + ':</strong> ' + escapeHtml(dims) + '</div>' +
                            '<div class="small mb-2"><strong>' + escapeHtml(i18n.uploader) + ':</strong> ' + escapeHtml(item.uploader || '-') + '</div>' +
                            '<div class="small mb-2"><strong>' + escapeHtml(i18n.date) + ':</strong> ' + escapeHtml(item.created_at_human || item.created_at || '-') + '</div>' +
                            '<div class="small mb-3">' +
                            '  <strong>' + escapeHtml(i18n.url) + ':</strong>' +
                            '  <div class="input-group input-group-sm mt-1">' +
                            '    <input type="text" class="form-control" readonly value="' + escapeAttribute(item.url || '') + '" data-ml-url-value>' +
                            '    <button type="button" class="btn btn-light-primary" data-ml-copy-url>' + escapeHtml(i18n.copy) + '</button>' +
                            '  </div>' +
                            '</div>' +
                            '<div class="d-flex flex-wrap gap-2">' +
                            '  <button type="button" class="btn btn-sm btn-light-success" data-ml-save>' + escapeHtml(i18n.saveDetails) + '</button>' +
                            '  <button type="button" class="btn btn-sm btn-light-danger" data-ml-delete>' + escapeHtml(i18n.deleteLabel) + '</button>' +
                            '</div>';

                        updateUseButton();
                    }

                    function loadFolders() {
                        return fetch(routes.folders, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                            .then(function (res) { return res.json(); })
                            .then(function (data) {
                                state.folders = data.folders || [];
                                if (state.folderId !== 'root' && !state.folders.some(function (folder) {
                                    return folder.id === parseInt(state.folderId, 10);
                                })) {
                                    state.folderId = 'root';
                                }
                                renderFolders();
                            });
                    }

                    function loadMedia(page) {
                        state.page = page || 1;
                        var el = $('[data-ml-items]');
                        if (el) {
                            el.innerHTML = '<div class="text-center text-muted py-15">' + escapeHtml(i18n.loading) + '</div>';
                        }

                        var url = routes.list +
                            '?page=' + encodeURIComponent(state.page) +
                            '&q=' + encodeURIComponent(state.search || '') +
                            '&folder_id=' + encodeURIComponent(state.folderId) +
                            '&type=' + encodeURIComponent(state.type || 'all') +
                            '&sort=' + encodeURIComponent(state.sort || 'newest') +
                            '&per_page=24';
                        if (state.date) {
                            url += '&date=' + encodeURIComponent(state.date);
                        }

                        return fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                            .then(function (res) { return res.json(); })
                            .then(function (data) {
                                renderItems(data.data || []);
                                renderPagination(data);
                            });
                    }

                    function resetSelection() {
                        state.selectedId = null;
                        state.selectedIds = [];
                        setBulkButtonState();
                        updateUseButton();
                    }

                    function reload() {
                        return loadFolders().then(function () {
                            return loadMedia(1);
                        });
                    }

                    function applyPickerResult(items) {
                        if (!items || !items.length) return;

                        if (typeof state.onSelect === 'function') {
                            state.onSelect(items);
                            closeModal();
                            return;
                        }

                        if (state.tinyEditor) {
                            var item = items[0];
                            var html = '<img src="' + escapeAttribute(item.url) + '"' +
                                ' alt="' + escapeAttribute(item.alt_text || '') + '"' +
                                ' title="' + escapeAttribute(item.title || '') + '"' +
                                ' data-media-path="' + escapeAttribute(item.path || '') + '">';
                            if (typeof state.tinyEditor.insertContent === 'function') {
                                state.tinyEditor.insertContent(html);
                            }
                            closeModal();
                            return;
                        }

                        var first = items[0];
                        if (state.targetInput) {
                            if (typeof state.targetInput === 'string') {
                                state.targetInput = document.querySelector(state.targetInput);
                            }
                            if (state.targetInput) {
                                state.targetInput.value = first.path;
                                var imageRoot = state.targetInput.closest('.admin-image-input');
                                if (imageRoot && typeof window.adminImageInputClearRemoveFor === 'function') {
                                    window.adminImageInputClearRemoveFor(imageRoot);
                                }
                            }
                        }
                        if (state.targetPreview) {
                            if (typeof state.targetPreview === 'string') {
                                state.targetPreview = document.querySelector(state.targetPreview);
                            }
                            if (state.targetPreview) {
                                state.targetPreview.style.backgroundImage = "url('" + first.url + "')";
                            }
                        }
                        closeModal();
                    }

                    function useSelection() {
                        if (state.mode === 'picker-multi') {
                            applyPickerResult(getOrderedSelectedItems());
                            return;
                        }
                        var item = getSelectedItem();
                        if (item) applyPickerResult([item]);
                    }

                    function openModal() {
                        var modal = modalEl();
                        if (!modal || typeof bootstrap === 'undefined') return;
                        bootstrap.Modal.getOrCreateInstance(modal).show();
                    }

                    function closeModal() {
                        var modal = modalEl();
                        if (!modal || typeof bootstrap === 'undefined') return;
                        bootstrap.Modal.getOrCreateInstance(modal).hide();
                        state.targetInput = null;
                        state.targetPreview = null;
                        state.onSelect = null;
                        state.tinyEditor = null;
                        state.max = null;
                        state.mode = 'manage';
                        updateUseButton();
                    }

                    function openAsPicker(options) {
                        options = options || {};
                        state.mode = options.mode === 'multi' || options.mode === 'picker-multi' ? 'picker-multi' : 'picker';
                        state.onSelect = typeof options.onSelect === 'function' ? options.onSelect : null;
                        state.targetInput = options.targetInput || null;
                        state.targetPreview = options.targetPreview || null;
                        state.tinyEditor = options.tinyEditor || null;
                        state.max = options.max || null;
                        resetSelection();
                        updateUseButton();
                        openModal();
                        reload();
                    }

                    function openAsManage() {
                        state.mode = 'manage';
                        state.onSelect = null;
                        state.targetInput = null;
                        state.targetPreview = null;
                        state.tinyEditor = null;
                        state.max = null;
                        resetSelection();
                        updateUseButton();
                        if (id === 'modal') {
                            openModal();
                        }
                        reload();
                    }

                    function setChecked(mediaId, checked) {
                        var index = state.selectedIds.indexOf(mediaId);
                        if (checked) {
                            if (index === -1) {
                                if (state.max && state.selectedIds.length >= state.max) {
                                    return false;
                                }
                                state.selectedIds.push(mediaId);
                            }
                        } else if (index !== -1) {
                            state.selectedIds.splice(index, 1);
                        }
                        state.selectedId = mediaId;
                        setBulkButtonState();
                        updateUseButton();
                        return true;
                    }

                    function refreshSelectionChrome() {
                        root.querySelectorAll('[data-ml-item]').forEach(function (el) {
                            var mediaId = parseInt(el.getAttribute('data-ml-item'), 10);
                            var selected = state.selectedId === mediaId;
                            el.classList.toggle('is-selected', selected);
                            el.classList.toggle('table-active', selected);
                            var checkbox = el.querySelector('[data-ml-check]');
                            if (checkbox) {
                                checkbox.checked = isChecked(mediaId);
                            }
                        });
                        renderDetails(getSelectedItem());
                    }

                    function saveCurrentMetadata() {
                        var item = getSelectedItem();
                        if (!item) return;

                        var nameInput = $('[data-ml-field="name"]');
                        var altInput = $('[data-ml-field="alt_text"]');
                        var titleInput = $('[data-ml-field="title"]');
                        var captionInput = $('[data-ml-field="caption"]');
                        var folderInput = $('[data-ml-field="folder_id"]');
                        var folderValue = folderInput && folderInput.value ? parseInt(folderInput.value, 10) : null;

                        var payload = {
                            name: nameInput ? (nameInput.value || null) : null,
                            alt_text: altInput ? (altInput.value || null) : null,
                            title: titleInput ? (titleInput.value || null) : null,
                            caption: captionInput ? (captionInput.value || null) : null,
                            folder_id: folderValue,
                        };

                        var updateUrl = routes.update.replace('__id__', item.id);
                        fetch(updateUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                        })
                            .then(function (res) {
                                if (!res.ok) throw new Error(i18n.unableSave);
                                return res.json();
                            })
                            .then(function (data) {
                                var index = state.items.findIndex(function (media) { return media.id === item.id; });
                                if (index !== -1) state.items[index] = data.item;
                                state.selectedId = data.item.id;
                                if (payload.folder_id !== item.folder_id) {
                                    loadFolders().then(function () { loadMedia(state.page); });
                                } else {
                                    renderItems(state.items);
                                    renderFolders();
                                }
                            })
                            .catch(function (error) {
                                alert(error.message);
                            });
                    }

                    function copyCurrentUrl() {
                        var item = getSelectedItem();
                        if (!item) return;
                        var done = function () {
                            var btn = $('[data-ml-copy-url]');
                            if (!btn) return;
                            var original = btn.textContent;
                            btn.textContent = i18n.copied;
                            setTimeout(function () { btn.textContent = original; }, 1200);
                        };
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(item.url).then(done).catch(function () {});
                        }
                    }

                    function createFolder() {
                        var input = $('[data-ml-new-folder]');
                        var name = input ? input.value.trim() : '';
                        if (!name) return;

                        fetch(routes.storeFolder, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({name: name}),
                        })
                            .then(function (res) {
                                if (!res.ok) {
                                    return res.json().then(function (data) {
                                        throw new Error(data.message || i18n.unableCreateFolder);
                                    });
                                }
                                return res.json();
                            })
                            .then(function (data) {
                                if (input) input.value = '';
                                state.folderId = String(data.folder.id);
                                resetSelection();
                                return reload();
                            })
                            .catch(function (error) {
                                alert(error.message);
                            });
                    }

                    function deleteCurrentFolder() {
                        if (state.folderId === 'root') return;
                        if (!confirm(i18n.deleteFolderConfirm)) return;

                        fetch(routes.destroyFolder.replace('__id__', state.folderId), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }).then(function (res) {
                            if (!res.ok) throw new Error(i18n.unableDeleteFolder);
                            state.folderId = 'root';
                            resetSelection();
                            return reload();
                        }).catch(function (error) {
                            alert(error.message);
                        });
                    }

                    function deleteCurrentItem() {
                        var item = getSelectedItem();
                        if (!item) return;
                        if (!confirm(i18n.trashConfirm)) return;

                        fetch(routes.destroy.replace('__id__', item.id), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }).then(function () {
                            state.selectedIds = state.selectedIds.filter(function (mediaId) {
                                return mediaId !== item.id;
                            });
                            state.selectedId = null;
                            loadFolders();
                            loadMedia(state.page);
                        });
                    }

                    function deleteSelectedItems() {
                        if (!state.selectedIds.length) return;
                        if (!confirm(i18n.trashConfirm)) return;

                        fetch(routes.deleteMulti, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ids: state.selectedIds}),
                        }).then(function () {
                            resetSelection();
                            loadFolders();
                            loadMedia(state.page);
                        });
                    }

                    function uploadFiles(fileList) {
                        if (!fileList || fileList.length === 0) return Promise.resolve();
                        var form = new FormData();
                        Array.prototype.forEach.call(fileList, function (file) {
                            form.append('files[]', file);
                        });
                        if (state.folderId !== 'root') {
                            form.append('folder_id', state.folderId);
                        }
                        var alt = $('[data-ml-upload-alt]');
                        var title = $('[data-ml-upload-title]');
                        var caption = $('[data-ml-upload-caption]');
                        form.append('alt_text', alt ? alt.value || '' : '');
                        form.append('title', title ? title.value || '' : '');
                        form.append('caption', caption ? caption.value || '' : '');

                        return fetch(routes.store, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest'},
                            body: form,
                        }).then(function (res) {
                            if (!res.ok) {
                                return res.json().then(function (data) {
                                    throw new Error(data.message || i18n.uploadFailed);
                                });
                            }
                            return res.json();
                        }).then(function () {
                            return reload();
                        }).catch(function (error) {
                            alert(error.message);
                        });
                    }

                    function bindUi() {
                        updateViewToggle();
                        updateUseButton();

                        root.addEventListener('click', function (event) {
                            if (event.target.closest('.imas-ml-check') || event.target.closest('[data-ml-check]')) {
                                event.stopPropagation();
                                return;
                            }

                            var item = event.target.closest('[data-ml-item]');
                            if (item && root.contains(item)) {
                                state.selectedId = parseInt(item.getAttribute('data-ml-item'), 10);
                                refreshSelectionChrome();
                                return;
                            }

                            var folder = event.target.closest('[data-ml-folder]');
                            if (folder && root.contains(folder)) {
                                state.folderId = folder.getAttribute('data-ml-folder');
                                resetSelection();
                                renderFolders();
                                loadMedia(1);
                                return;
                            }

                            var pageBtn = event.target.closest('[data-ml-page]');
                            if (pageBtn && root.contains(pageBtn) && !pageBtn.classList.contains('disabled')) {
                                loadMedia(parseInt(pageBtn.getAttribute('data-ml-page'), 10));
                                return;
                            }

                            var viewBtn = event.target.closest('[data-ml-view]');
                            if (viewBtn && root.contains(viewBtn)) {
                                state.view = viewBtn.getAttribute('data-ml-view') === 'list' ? 'list' : 'grid';
                                setStoredView(state.view);
                                updateViewToggle();
                                renderItems(state.items);
                                return;
                            }

                            if (event.target.closest('[data-ml-create-folder]')) {
                                createFolder();
                                return;
                            }
                            if (event.target.closest('[data-ml-delete-folder]')) {
                                deleteCurrentFolder();
                                return;
                            }
                            if (event.target.closest('[data-ml-save]')) {
                                saveCurrentMetadata();
                                return;
                            }
                            if (event.target.closest('[data-ml-copy-url]')) {
                                copyCurrentUrl();
                                return;
                            }
                            if (event.target.closest('[data-ml-delete]')) {
                                deleteCurrentItem();
                                return;
                            }
                            if (event.target.closest('[data-ml-bulk-delete]')) {
                                deleteSelectedItems();
                                return;
                            }
                            if (event.target.closest('[data-ml-use]')) {
                                useSelection();
                            }
                        });

                        root.addEventListener('change', function (event) {
                            var check = event.target.closest('[data-ml-check]');
                            if (!check || check.type !== 'checkbox') return;
                            var mediaId = parseInt(check.getAttribute('data-ml-check'), 10);
                            var ok = setChecked(mediaId, check.checked);
                            if (!ok) {
                                check.checked = false;
                            }
                            refreshSelectionChrome();
                        });

                        var search = $('[data-ml-search]');
                        if (search) {
                            search.addEventListener('input', function () {
                                clearTimeout(state.searchTimer);
                                state.searchTimer = setTimeout(function () {
                                    state.search = search.value || '';
                                    loadMedia(1);
                                }, 300);
                            });
                        }

                        var typeFilter = $('[data-ml-type]');
                        if (typeFilter) {
                            typeFilter.addEventListener('change', function () {
                                state.type = typeFilter.value || 'all';
                                loadMedia(1);
                            });
                        }

                        var dateFilter = $('[data-ml-date]');
                        if (dateFilter) {
                            dateFilter.addEventListener('change', function () {
                                state.date = dateFilter.value || '';
                                loadMedia(1);
                            });
                        }

                        var sortFilter = $('[data-ml-sort]');
                        if (sortFilter) {
                            sortFilter.addEventListener('change', function () {
                                state.sort = sortFilter.value || 'newest';
                                loadMedia(1);
                            });
                        }

                        var uploadInput = $('[data-ml-upload]');
                        if (uploadInput) {
                            uploadInput.addEventListener('change', function () {
                                uploadFiles(uploadInput.files).then(function () {
                                    uploadInput.value = '';
                                });
                            });
                        }

                        var folderNameInput = $('[data-ml-new-folder]');
                        if (folderNameInput) {
                            folderNameInput.addEventListener('keydown', function (event) {
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    createFolder();
                                }
                            });
                        }

                        var zone = $('[data-ml-dropzone]');
                        if (zone) {
                            ['dragenter', 'dragover'].forEach(function (eventName) {
                                zone.addEventListener(eventName, function (event) {
                                    event.preventDefault();
                                    zone.classList.add('is-dragover');
                                });
                            });
                            ['dragleave', 'drop'].forEach(function (eventName) {
                                zone.addEventListener(eventName, function (event) {
                                    event.preventDefault();
                                    zone.classList.remove('is-dragover');
                                });
                            });
                            zone.addEventListener('drop', function (event) {
                                uploadFiles(event.dataTransfer ? event.dataTransfer.files : null);
                            });
                        }
                    }

                    var api = {
                        id: id,
                        shouldAutoload: root.getAttribute('data-imas-ml-autoload') === '1',
                        root: root,
                        state: state,
                        boot: function () {
                            state.mode = 'manage';
                            resetSelection();
                            updateViewToggle();
                            updateUseButton();
                            return reload();
                        },
                        openAsManage: openAsManage,
                        openAsPicker: openAsPicker,
                        closeModal: closeModal,
                        reload: reload,
                    };

                    bindUi();
                    instances[id] = api;
                    return api;
                }

                function modalInstance() {
                    return instances.modal || null;
                }

                function bindGlobalTriggers() {
                    document.addEventListener('click', function (event) {
                        var openBtn = event.target.closest('[data-media-open="true"]');
                        if (openBtn) {
                            var api = modalInstance();
                            if (api) api.openAsManage();
                            return;
                        }

                        var pickerBtn = event.target.closest('[data-media-picker-target]');
                        if (pickerBtn) {
                            var api = modalInstance();
                            if (!api) return;
                            var inputSelector = pickerBtn.getAttribute('data-media-picker-target');
                            var previewSelector = pickerBtn.getAttribute('data-media-preview-target');
                            api.openAsPicker({
                                mode: 'single',
                                targetInput: document.querySelector(inputSelector),
                                targetPreview: previewSelector ? document.querySelector(previewSelector) : null,
                            });
                        }
                    });
                }

                window.openMediaLibraryPicker = function (options) {
                    var api = modalInstance();
                    if (!api) return;
                    options = options || {};
                    api.openAsPicker({
                        mode: options.mode === 'multi' ? 'multi' : 'single',
                        onSelect: options.onSelect || null,
                        targetInput: options.targetInput || null,
                        targetPreview: options.targetPreview || null,
                        max: options.max || null,
                    });
                };

                window.openMediaLibraryForTinyMce = function (editor) {
                    var api = modalInstance();
                    if (!api) return;
                    api.openAsPicker({
                        mode: 'single',
                        tinyEditor: editor || null,
                    });
                };

                window.ImasMediaLibrary = {
                    instances: instances,
                    initAll: function () {
                        document.querySelectorAll('[data-imas-ml-instance]').forEach(function (root) {
                            createInstance(root);
                        });
                        Object.keys(instances).forEach(function (key) {
                            if (instances[key].shouldAutoload) {
                                instances[key].boot();
                            }
                        });
                    },
                };

                document.addEventListener('DOMContentLoaded', function () {
                    bindGlobalTriggers();
                    window.ImasMediaLibrary.initAll();
                });
            })();
        </script>
    @endpush
@endonce

<div class="imas-media-library"
     data-imas-ml-instance="{{ $instance }}"
     data-imas-ml-autoload="{{ $autoload ? '1' : '0' }}">
    <div class="imas-ml-toolbar d-flex flex-wrap gap-2 align-items-center mb-4">
        <input type="search"
               class="form-control form-control-solid w-200px"
               data-ml-search
               placeholder="{{ __('Search') }}...">

        <select class="form-select form-select-solid w-150px" data-ml-type>
            <option value="all">{{ __('All media') }}</option>
            <option value="image">{{ __('Images') }}</option>
            <option value="jpeg">JPEG</option>
            <option value="png">PNG</option>
            <option value="gif">GIF</option>
            <option value="webp">WebP</option>
        </select>

        <input type="month" class="form-control form-control-solid w-160px" data-ml-date title="{{ __('Date') }}">

        <select class="form-select form-select-solid w-160px" data-ml-sort>
            <option value="newest">{{ __('Newest') }}</option>
            <option value="oldest">{{ __('Oldest') }}</option>
            <option value="name_asc">{{ __('Name A–Z') }}</option>
            <option value="name_desc">{{ __('Name Z–A') }}</option>
        </select>

        <div class="btn-group" role="group" aria-label="{{ __('View') }}">
            <button type="button" class="btn btn-sm btn-primary" data-ml-view="grid" title="{{ __('Grid') }}">
                <i class="bi bi-grid-3x3-gap"></i>
            </button>
            <button type="button" class="btn btn-sm btn-light" data-ml-view="list" title="{{ __('List') }}">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>

        <label class="btn btn-light-primary mb-0">
            <i class="bi bi-upload me-1"></i>{{ __('Upload') }}
            <input type="file" class="d-none" data-ml-upload accept=".png,.jpg,.jpeg,.webp,.gif" multiple>
        </label>

        <button type="button" class="btn btn-light-danger" data-ml-bulk-delete disabled>
            {{ __('Delete Selected') }}
        </button>

        <span class="text-muted ms-auto">
            {{ __('Current folder') }}:
            <strong data-ml-current-folder>{{ __('Root') }}</strong>
        </span>
    </div>

    <div class="imas-ml-dropzone border border-dashed rounded p-4 text-center text-muted mb-4" data-ml-dropzone>
        {{ __('Drop images here to upload multiple files') }}
    </div>

    <div class="card card-flush border mb-4">
        <div class="card-header min-h-40px">
            <h5 class="mb-0">{{ __('Details applied to uploaded images') }}</h5>
        </div>
        <div class="card-body py-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Alt text') }}</label>
                    <input type="text" class="form-control form-control-sm" data-ml-upload-alt maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Title') }}</label>
                    <input type="text" class="form-control form-control-sm" data-ml-upload-title maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Caption') }}</label>
                    <input type="text" class="form-control form-control-sm" data-ml-upload-caption maxlength="2000">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 imas-ml-workspace">
        <div class="col-xl-2 col-lg-3">
            <div class="card card-flush border h-100">
                <div class="card-header min-h-40px">
                    <h5 class="mb-0">{{ __('Folders') }}</h5>
                </div>
                <div class="card-body p-3">
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" data-ml-new-folder maxlength="120" placeholder="{{ __('New folder') }}">
                        <button type="button" class="btn btn-light-primary" data-ml-create-folder title="{{ __('Create folder') }}">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="list-group list-group-flush mb-3 imas-ml-folders" data-ml-folders></div>
                    <button type="button" class="btn btn-sm btn-light-danger w-100" data-ml-delete-folder disabled>
                        {{ __('Delete folder') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="col-xl-7 col-lg-5">
            <div class="card card-flush border h-100">
                <div class="card-body p-4">
                    <div class="imas-ml-items imas-ml-items--grid" data-ml-items>
                        <div class="text-center text-muted py-15">{{ __('Loading...') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4">
            <div class="card card-flush border h-100">
                <div class="card-header min-h-40px">
                    <h5 class="mb-0">{{ __('Attachment Details') }}</h5>
                </div>
                <div class="card-body" data-ml-details>
                    <div class="text-muted">{{ __('Select an image to see details.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-5">
        <div data-ml-pagination></div>
        <div data-ml-use-wrap class="d-none">
            <button type="button" class="btn btn-primary" data-ml-use disabled>
                {{ __('Use this image') }}
            </button>
        </div>
    </div>
</div>
