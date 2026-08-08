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
                    updateFolder: @json(route('admin.media_library.folders.update', ['folder' => '__id__'])),
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
                    emptyFolder: @json(__('This folder is empty.')),
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
                    folders: @json(__('Folders')),
                    files: @json(__('Files')),
                    saveDetails: @json(__('Save details')),
                    rename: @json(__('Rename')),
                    renameFolder: @json(__('Edit folder')),
                    renamePrompt: @json(__('Enter a new name')),
                    openFolder: @json(__('Open folder')),
                    itemsCount: @json(__(':count items')),
                    deleteLabel: @json(__('Delete')),
                    trashConfirm: @json(__('Move to trash / remove from library?')),
                    deleteFolderConfirm: @json(__('Delete this folder, its subfolders, and every image inside them?')),
                    unableSave: @json(__('Unable to save image details.')),
                    unableCreateFolder: @json(__('Unable to create folder.')),
                    unableRenameFolder: @json(__('Unable to save folder.')),
                    unableDeleteFolder: @json(__('Unable to delete folder.')),
                    uploadFailed: @json(__('Upload failed.')),
                    copied: @json(__('Copied')),
                    prev: @json(__('Prev')),
                    next: @json(__('Next')),
                    parentFolder: @json(__('Parent folder')),
                    newFolder: @json(__('New folder')),
                    editFolder: @json(__('Edit folder')),
                    folderName: @json(__('Folder name')),
                    expand: @json(__('Expand')),
                    collapse: @json(__('Collapse')),
                };

                var folderModalState = {
                    instanceId: null,
                    mode: 'edit',
                    folderId: null,
                    bound: false,
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
                        expandedFolderIds: {},
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

                    function findFolder(folderId) {
                        var idNum = parseInt(folderId, 10);
                        if (!idNum) return null;
                        return state.folders.find(function (item) {
                            return item.id === idNum;
                        }) || null;
                    }

                    function currentFolderName() {
                        if (state.folderId === 'root') return i18n.root;
                        var folder = findFolder(state.folderId);
                        return folder ? folder.name : i18n.root;
                    }

                    function currentParentId() {
                        if (state.folderId === 'root') return null;
                        return parseInt(state.folderId, 10) || null;
                    }

                    function folderParentKey(folder) {
                        return folder.parent_id == null ? null : parseInt(folder.parent_id, 10);
                    }

                    function childFoldersOf(parentId) {
                        var parentKey = parentId == null || parentId === 'root' ? null : parseInt(parentId, 10);
                        return state.folders.filter(function (folder) {
                            return folderParentKey(folder) === parentKey;
                        });
                    }

                    function folderDepth(folder) {
                        var depth = 0;
                        var pid = folderParentKey(folder);
                        var guard = 0;
                        while (pid != null && guard < 50) {
                            depth++;
                            var parent = findFolder(pid);
                            pid = parent ? folderParentKey(parent) : null;
                            guard++;
                        }
                        return depth;
                    }

                    function folderAncestorIds(folderId) {
                        var ids = [];
                        var folder = findFolder(folderId);
                        var guard = 0;
                        while (folder && folder.parent_id != null && guard < 50) {
                            ids.push(parseInt(folder.parent_id, 10));
                            folder = findFolder(folder.parent_id);
                            guard++;
                        }
                        return ids;
                    }

                    function ensureExpandedPath(folderId) {
                        folderAncestorIds(folderId).forEach(function (ancestorId) {
                            state.expandedFolderIds[ancestorId] = true;
                        });
                        if (folderId && folderId !== 'root') {
                            state.expandedFolderIds[parseInt(folderId, 10)] = true;
                        }
                    }

                    function directoryFolders() {
                        var parentId = state.folderId === 'root' ? null : parseInt(state.folderId, 10);
                        var q = (state.search || '').trim().toLowerCase();
                        return childFoldersOf(parentId).filter(function (folder) {
                            if (!q) return true;
                            return String(folder.name || '').toLowerCase().indexOf(q) !== -1;
                        });
                    }

                    function parentDirectoryId() {
                        if (state.folderId === 'root') return 'root';
                        var folder = findFolder(state.folderId);
                        if (!folder || folder.parent_id == null) return 'root';
                        return String(folder.parent_id);
                    }

                    function breadcrumbTrail() {
                        if (state.folderId === 'root') return [];
                        var trail = [];
                        var folder = findFolder(state.folderId);
                        var guard = 0;
                        while (folder && guard < 50) {
                            trail.unshift(folder);
                            folder = folder.parent_id != null ? findFolder(folder.parent_id) : null;
                            guard++;
                        }
                        return trail;
                    }

                    function itemsCountLabel(count) {
                        return String(i18n.itemsCount).replace(':count', String(count));
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

                    function openDirectory(folderId) {
                        state.folderId = String(folderId);
                        if (folderId !== 'root') {
                            ensureExpandedPath(folderId);
                        }
                        resetSelection();
                        renderFolders();
                        renderBreadcrumb();
                        loadMedia(1);
                    }

                    function renderBreadcrumb() {
                        var el = $('[data-ml-breadcrumb]');
                        if (!el) return;

                        var trail = breadcrumbTrail();
                        var html = '<nav class="imas-ml-breadcrumb" aria-label="breadcrumb">' +
                            '<ol class="breadcrumb mb-0">' +
                            '<li class="breadcrumb-item">' +
                            '<button type="button" class="btn btn-link p-0 align-baseline" data-ml-folder="root">' +
                            '<i class="bi bi-house-door me-1"></i>' + escapeHtml(i18n.root) +
                            '</button></li>';

                        trail.forEach(function (folder, index) {
                            var isLast = index === trail.length - 1;
                            if (isLast) {
                                html += '<li class="breadcrumb-item active" aria-current="page">' +
                                    '<i class="bi bi-folder2 me-1"></i>' + escapeHtml(folder.name) +
                                    '</li>';
                            } else {
                                html += '<li class="breadcrumb-item">' +
                                    '<button type="button" class="btn btn-link p-0 align-baseline" data-ml-folder="' + folder.id + '">' +
                                    '<i class="bi bi-folder2 me-1"></i>' + escapeHtml(folder.name) +
                                    '</button></li>';
                            }
                        });

                        html += '</ol></nav>';
                        el.innerHTML = html;
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

                        if (state.folderId !== 'root') {
                            ensureExpandedPath(state.folderId);
                        }

                        var rootActive = state.folderId === 'root' ? 'active' : '';
                        var html = '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ' + rootActive + '" data-ml-folder="root">' +
                            '<span><i class="bi bi-folder2-open me-2"></i>' + escapeHtml(i18n.root) + '</span>' +
                            '</button>';

                        function appendTree(parentId, depth) {
                            childFoldersOf(parentId).forEach(function (folder) {
                                var children = childFoldersOf(folder.id);
                                var hasChildren = children.length > 0;
                                var expanded = !!state.expandedFolderIds[folder.id];
                                var active = parseInt(state.folderId, 10) === folder.id ? 'active' : '';
                                var pad = Math.max(0, depth) * 14;

                                html += '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-1 imas-ml-folder-row ' + active + '" data-ml-folder-row="' + folder.id + '">' +
                                    '<div class="d-flex align-items-center flex-grow-1 min-w-0" style="padding-inline-start:' + pad + 'px">' +
                                    (hasChildren
                                        ? '<button type="button" class="btn btn-icon btn-sm btn-light imas-ml-folder-toggle" data-ml-toggle-folder="' + folder.id + '" title="' + escapeAttribute(expanded ? i18n.collapse : i18n.expand) + '">' +
                                          '<i class="bi ' + (expanded ? 'bi-caret-down-fill' : 'bi-caret-right-fill') + '"></i>' +
                                          '</button>'
                                        : '<span class="imas-ml-folder-toggle-spacer"></span>') +
                                    '<button type="button" class="btn btn-link text-start text-dark text-decoration-none p-0 flex-grow-1 text-truncate" data-ml-folder="' + folder.id + '">' +
                                    '<i class="bi bi-folder' + (hasChildren && expanded ? '2-open' : '') + ' me-2"></i>' + escapeHtml(folder.name) +
                                    '</button>' +
                                    '</div>' +
                                    '<span class="badge badge-light-primary">' + folder.media_count + '</span>' +
                                    '<button type="button" class="btn btn-icon btn-sm btn-light" data-ml-rename-folder="' + folder.id + '" title="' + escapeAttribute(i18n.renameFolder) + '">' +
                                    '<i class="bi bi-pencil"></i>' +
                                    '</button>' +
                                    '</div>';

                                if (hasChildren && expanded) {
                                    appendTree(folder.id, depth + 1);
                                }
                            });
                        }

                        appendTree(null, 0);
                        el.innerHTML = html;

                        var current = $('[data-ml-current-folder]');
                        if (current) current.textContent = currentFolderName();

                        var deleteBtn = $('[data-ml-delete-folder]');
                        if (deleteBtn) deleteBtn.disabled = state.folderId === 'root';

                        renderBreadcrumb();
                    }

                    function renderFolderGrid(folders) {
                        return folders.map(function (folder) {
                            return '' +
                                '<div class="imas-ml-grid__item imas-ml-grid__item--folder" data-ml-dir-folder="' + folder.id + '" title="' + escapeAttribute(i18n.openFolder) + '">' +
                                '  <div class="imas-ml-grid__thumb imas-ml-grid__thumb--folder">' +
                                '    <i class="bi bi-folder-fill"></i>' +
                                '  </div>' +
                                '  <div class="imas-ml-grid__meta text-truncate" title="' + escapeAttribute(folder.name || '') + '">' + escapeHtml(folder.name || '') + '</div>' +
                                '  <div class="imas-ml-grid__submeta text-truncate">' + escapeHtml(itemsCountLabel(folder.media_count || 0)) + '</div>' +
                                '</div>';
                        }).join('');
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
                                '  <div class="imas-ml-grid__meta text-truncate" data-ml-inline-rename="' + item.id + '" title="' + escapeAttribute((item.name || '') + ' — ' + i18n.rename) + '">' + escapeHtml(item.name || '') + '</div>' +
                                '</div>';
                        }).join('');
                    }

                    function renderFolderList(folders) {
                        return folders.map(function (folder) {
                            return '' +
                                '<tr class="imas-ml-list__folder" data-ml-dir-folder="' + folder.id + '" style="cursor:pointer" title="' + escapeAttribute(i18n.openFolder) + '">' +
                                '  <td style="width:40px"></td>' +
                                '  <td style="width:64px" class="text-center">' +
                                '    <span class="imas-ml-list__folder-icon"><i class="bi bi-folder-fill"></i></span>' +
                                '  </td>' +
                                '  <td class="fw-semibold">' +
                                '    <span class="me-2">' + escapeHtml(folder.name || '-') + '</span>' +
                                '    <button type="button" class="btn btn-icon btn-sm btn-light" data-ml-rename-folder="' + folder.id + '" title="' + escapeAttribute(i18n.renameFolder) + '" onclick="event.stopPropagation()">' +
                                '      <i class="bi bi-pencil"></i>' +
                                '    </button>' +
                                '  </td>' +
                                '  <td class="text-muted">' + escapeHtml(i18n.folder) + '</td>' +
                                '  <td class="text-muted">' + escapeHtml(itemsCountLabel(folder.media_count || 0)) + '</td>' +
                                '  <td class="text-muted">—</td>' +
                                '</tr>';
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
                                '  <td class="fw-semibold">' +
                                '    <span data-ml-inline-rename="' + item.id + '" title="' + escapeAttribute(i18n.rename) + '">' + escapeHtml(item.name || '-') + '</span>' +
                                '  </td>' +
                                '  <td class="text-muted">' + escapeHtml(item.mime_type || '-') + '</td>' +
                                '  <td class="text-muted">' + formatSize(item.size) + '</td>' +
                                '  <td class="text-muted">' + escapeHtml(item.created_at_human || item.created_at || '-') + '</td>' +
                                '</tr>';
                        }).join('');

                        return rows;
                    }

                    function renderItems(items) {
                        var el = $('[data-ml-items]');
                        if (!el) return;

                        state.items = items || [];
                        var folders = directoryFolders();
                        var hasFolders = folders.length > 0;
                        var hasItems = state.items.length > 0;

                        if (!hasFolders && !hasItems) {
                            var emptyMsg = state.folderId === 'root' ? i18n.noMedia : i18n.emptyFolder;
                            el.innerHTML = '<div class="text-center text-muted py-15">' + escapeHtml(emptyMsg) + '</div>';
                            renderDetails(null);
                            setBulkButtonState();
                            updateUseButton();
                            return;
                        }

                        if (state.view === 'list') {
                            el.className = 'imas-ml-items imas-ml-items--list';
                            el.innerHTML = '' +
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
                                '    <tbody>' +
                                (state.folderId !== 'root'
                                    ? '<tr class="imas-ml-list__folder" data-ml-folder="' + escapeAttribute(parentDirectoryId()) + '" style="cursor:pointer">' +
                                      '  <td></td><td class="text-center"><span class="imas-ml-list__folder-icon"><i class="bi bi-arrow-up-left"></i></span></td>' +
                                      '  <td class="fw-semibold">..</td>' +
                                      '  <td class="text-muted">' + escapeHtml(i18n.parentFolder) + '</td>' +
                                      '  <td></td><td></td>' +
                                      '</tr>'
                                    : '') +
                                renderFolderList(folders) +
                                renderList(state.items) +
                                '    </tbody>' +
                                '  </table>' +
                                '</div>';
                        } else {
                            el.className = 'imas-ml-items imas-ml-items--grid';
                            var gridHtml = '';
                            if (state.folderId !== 'root') {
                                gridHtml += '' +
                                    '<div class="imas-ml-grid__item imas-ml-grid__item--folder imas-ml-grid__item--up" data-ml-folder="' + escapeAttribute(parentDirectoryId()) + '" title="' + escapeAttribute(i18n.parentFolder) + '">' +
                                    '  <div class="imas-ml-grid__thumb imas-ml-grid__thumb--folder">' +
                                    '    <i class="bi bi-arrow-up-left"></i>' +
                                    '  </div>' +
                                    '  <div class="imas-ml-grid__meta text-truncate">..</div>' +
                                    '  <div class="imas-ml-grid__submeta text-truncate">' + escapeHtml(i18n.parentFolder) + '</div>' +
                                    '</div>';
                            }
                            gridHtml += renderFolderGrid(folders) + renderGrid(state.items);
                            el.innerHTML = gridHtml;
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

                    function folderOptionsHtml(selectedFolderId, options) {
                        options = options || {};
                        var excludeIds = options.excludeIds || [];
                        var rootSelected = selectedFolderId == null || selectedFolderId === '' ? ' selected' : '';
                        var html = '<option value=""' + rootSelected + '>' + escapeHtml(i18n.root) + '</option>';
                        state.folders.forEach(function (folder) {
                            if (excludeIds.indexOf(folder.id) !== -1) return;
                            var depth = folderDepth(folder);
                            var prefix = depth > 0 ? Array(depth + 1).join('— ') : '';
                            var selected = selectedFolderId === folder.id ? ' selected' : '';
                            html += '<option value="' + folder.id + '"' + selected + '>' +
                                escapeHtml(prefix + folder.name) +
                                '</option>';
                        });
                        return html;
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
                            '  <label class="form-label d-flex justify-content-between align-items-center">' +
                            '    <span>' + escapeHtml(i18n.name) + '</span>' +
                            '    <button type="button" class="btn btn-link btn-sm p-0" data-ml-focus-name>' + escapeHtml(i18n.rename) + '</button>' +
                            '  </label>' +
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

                        var parentId = currentParentId();
                        fetch(routes.storeFolder, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                name: name,
                                parent_id: parentId,
                            }),
                        })
                            .then(function (res) {
                                if (!res.ok) {
                                    return res.json().then(function (data) {
                                        var message = data.message || i18n.unableCreateFolder;
                                        if (data.errors && data.errors.name && data.errors.name[0]) {
                                            message = data.errors.name[0];
                                        }
                                        throw new Error(message);
                                    });
                                }
                                return res.json();
                            })
                            .then(function (data) {
                                if (input) input.value = '';
                                if (parentId) {
                                    state.expandedFolderIds[parentId] = true;
                                }
                                state.folderId = String(data.folder.id);
                                resetSelection();
                                return reload();
                            })
                            .catch(function (error) {
                                alert(error.message);
                            });
                    }

                    function openFolderEditor(folderId) {
                        var folder = findFolder(folderId);
                        if (!folder) return;

                        var modal = document.getElementById('kt_modal_media_folder');
                        if (!modal) {
                            // Fallback if modal markup is missing.
                            renameFolderByIdPrompt(folder);
                            return;
                        }

                        bindFolderModalOnce();
                        folderModalState.instanceId = state.id;
                        folderModalState.mode = 'edit';
                        folderModalState.folderId = folder.id;

                        var title = modal.querySelector('[data-ml-folder-modal-title]');
                        var nameInput = modal.querySelector('[data-ml-folder-modal-name]');
                        var parentSelect = modal.querySelector('[data-ml-folder-modal-parent]');
                        var nameError = modal.querySelector('[data-ml-folder-modal-name-error]');
                        var parentError = modal.querySelector('[data-ml-folder-modal-parent-error]');

                        if (title) title.textContent = i18n.editFolder;
                        if (nameInput) {
                            nameInput.value = folder.name || '';
                            nameInput.classList.remove('is-invalid');
                        }
                        if (nameError) nameError.textContent = '';
                        if (parentError) parentError.textContent = '';

                        var blocked = [folder.id].concat(
                            state.folders
                                .filter(function (item) {
                                    return folderAncestorIds(item.id).indexOf(folder.id) !== -1;
                                })
                                .map(function (item) { return item.id; })
                        );

                        if (parentSelect) {
                            parentSelect.innerHTML = folderOptionsHtml(
                                folder.parent_id == null ? null : folder.parent_id,
                                {excludeIds: blocked}
                            );
                        }

                        var bsModal = window.bootstrap && window.bootstrap.Modal
                            ? window.bootstrap.Modal.getOrCreateInstance(modal)
                            : null;
                        if (bsModal) {
                            bsModal.show();
                        } else if (window.jQuery) {
                            window.jQuery(modal).modal('show');
                        }
                    }

                    function renameFolderByIdPrompt(folder) {
                        var nextName = window.prompt(i18n.renamePrompt, folder.name || '');
                        if (nextName === null) return;
                        nextName = String(nextName).trim();
                        if (!nextName || nextName === folder.name) return;
                        submitFolderUpdate(folder.id, {
                            name: nextName,
                            parent_id: folder.parent_id,
                        });
                    }

                    function submitFolderUpdate(folderId, payload) {
                        return fetch(routes.updateFolder.replace('__id__', folderId), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payload),
                        })
                            .then(function (res) {
                                if (!res.ok) {
                                    return res.json().then(function (data) {
                                        var message = data.message || i18n.unableRenameFolder;
                                        if (data.errors) {
                                            if (data.errors.name && data.errors.name[0]) message = data.errors.name[0];
                                            else if (data.errors.parent_id && data.errors.parent_id[0]) message = data.errors.parent_id[0];
                                        }
                                        var err = new Error(message);
                                        err.payload = data;
                                        throw err;
                                    });
                                }
                                return res.json();
                            })
                            .then(function (data) {
                                var index = state.folders.findIndex(function (item) {
                                    return item.id === folderId;
                                });
                                if (index !== -1) {
                                    state.folders[index].name = data.folder.name;
                                    state.folders[index].parent_id = data.folder.parent_id;
                                    state.folders[index].media_count = data.folder.media_count;
                                }
                                if (data.folder.parent_id) {
                                    state.expandedFolderIds[data.folder.parent_id] = true;
                                }
                                return loadFolders().then(function () {
                                    renderFolders();
                                    renderItems(state.items);
                                    return data;
                                });
                            });
                    }

                    function renameFolderById(folderId) {
                        openFolderEditor(folderId);
                    }

                    function setFolderModalSubmitting(isSubmitting) {
                        var modal = document.getElementById('kt_modal_media_folder');
                        if (!modal) return;
                        var submitBtn = modal.querySelector('[data-ml-folder-modal-action="submit"]');
                        if (!submitBtn) return;
                        if (isSubmitting) {
                            submitBtn.setAttribute('data-kt-indicator', 'on');
                            submitBtn.disabled = true;
                        } else {
                            submitBtn.removeAttribute('data-kt-indicator');
                            submitBtn.disabled = false;
                        }
                    }

                    function hideFolderModal() {
                        var modal = document.getElementById('kt_modal_media_folder');
                        if (!modal) return;
                        var bsModal = window.bootstrap && window.bootstrap.Modal
                            ? window.bootstrap.Modal.getInstance(modal)
                            : null;
                        if (bsModal) {
                            bsModal.hide();
                        } else if (window.jQuery) {
                            window.jQuery(modal).modal('hide');
                        }
                    }

                    function bindFolderModalOnce() {
                        if (folderModalState.bound) return;
                        var modal = document.getElementById('kt_modal_media_folder');
                        var form = document.getElementById('kt_modal_media_folder_form');
                        if (!modal || !form) return;
                        folderModalState.bound = true;

                        modal.querySelectorAll('[data-ml-folder-modal-action="close"], [data-ml-folder-modal-action="cancel"]').forEach(function (btn) {
                            btn.addEventListener('click', function (event) {
                                event.preventDefault();
                                hideFolderModal();
                            });
                        });

                        form.addEventListener('submit', function (event) {
                            event.preventDefault();
                            var owner = instances[folderModalState.instanceId];
                            if (!owner || typeof owner.submitFolderUpdate !== 'function' || folderModalState.mode !== 'edit' || !folderModalState.folderId) {
                                hideFolderModal();
                                return;
                            }

                            var nameInput = modal.querySelector('[data-ml-folder-modal-name]');
                            var parentSelect = modal.querySelector('[data-ml-folder-modal-parent]');
                            var nameError = modal.querySelector('[data-ml-folder-modal-name-error]');
                            var parentError = modal.querySelector('[data-ml-folder-modal-parent-error]');
                            var name = nameInput ? nameInput.value.trim() : '';
                            var parentValue = parentSelect ? parentSelect.value : '';

                            if (nameError) nameError.textContent = '';
                            if (parentError) parentError.textContent = '';
                            if (nameInput) nameInput.classList.remove('is-invalid');

                            if (!name) {
                                if (nameInput) nameInput.classList.add('is-invalid');
                                if (nameError) nameError.textContent = i18n.folderName;
                                return;
                            }

                            setFolderModalSubmitting(true);
                            owner.submitFolderUpdate(folderModalState.folderId, {
                                name: name,
                                parent_id: parentValue === '' ? null : parseInt(parentValue, 10),
                            }).then(function () {
                                hideFolderModal();
                            }).catch(function (error) {
                                var data = error.payload || {};
                                if (data.errors && data.errors.name && data.errors.name[0]) {
                                    if (nameInput) nameInput.classList.add('is-invalid');
                                    if (nameError) nameError.textContent = data.errors.name[0];
                                } else if (data.errors && data.errors.parent_id && data.errors.parent_id[0]) {
                                    if (parentError) parentError.textContent = data.errors.parent_id[0];
                                } else {
                                    alert(error.message || i18n.unableRenameFolder);
                                }
                            }).finally(function () {
                                setFolderModalSubmitting(false);
                            });
                        });
                    }

                    function renameMediaById(mediaId) {
                        var item = state.items.find(function (media) {
                            return media.id === parseInt(mediaId, 10);
                        });
                        if (!item) return;

                        var nextName = window.prompt(i18n.renamePrompt, item.name || '');
                        if (nextName === null) return;
                        nextName = String(nextName).trim();
                        if (!nextName || nextName === item.name) return;

                        fetch(routes.update.replace('__id__', item.id), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                name: nextName,
                                alt_text: item.alt_text,
                                title: item.title,
                                caption: item.caption,
                                folder_id: item.folder_id,
                            }),
                        })
                            .then(function (res) {
                                if (!res.ok) throw new Error(i18n.unableSave);
                                return res.json();
                            })
                            .then(function (data) {
                                var index = state.items.findIndex(function (media) {
                                    return media.id === item.id;
                                });
                                if (index !== -1) state.items[index] = data.item;
                                state.selectedId = data.item.id;
                                renderItems(state.items);
                            })
                            .catch(function (error) {
                                alert(error.message);
                            });
                    }

                    function deleteCurrentFolder() {
                        if (state.folderId === 'root') return;
                        if (!confirm(i18n.deleteFolderConfirm)) return;

                        var nextFolderId = parentDirectoryId();
                        fetch(routes.destroyFolder.replace('__id__', state.folderId), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }).then(function (res) {
                            if (!res.ok) throw new Error(i18n.unableDeleteFolder);
                            state.folderId = nextFolderId;
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

                            var toggleBtn = event.target.closest('[data-ml-toggle-folder]');
                            if (toggleBtn && root.contains(toggleBtn)) {
                                event.preventDefault();
                                event.stopPropagation();
                                var toggleId = parseInt(toggleBtn.getAttribute('data-ml-toggle-folder'), 10);
                                state.expandedFolderIds[toggleId] = !state.expandedFolderIds[toggleId];
                                renderFolders();
                                return;
                            }

                            var renameFolderBtn = event.target.closest('[data-ml-rename-folder]');
                            if (renameFolderBtn && root.contains(renameFolderBtn)) {
                                event.preventDefault();
                                event.stopPropagation();
                                renameFolderById(renameFolderBtn.getAttribute('data-ml-rename-folder'));
                                return;
                            }

                            var dirFolder = event.target.closest('[data-ml-dir-folder]');
                            if (dirFolder && root.contains(dirFolder)) {
                                openDirectory(dirFolder.getAttribute('data-ml-dir-folder'));
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
                                openDirectory(folder.getAttribute('data-ml-folder'));
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
                            if (event.target.closest('[data-ml-focus-name]')) {
                                var nameField = $('[data-ml-field="name"]');
                                if (nameField) {
                                    nameField.focus();
                                    nameField.select();
                                }
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

                        root.addEventListener('dblclick', function (event) {
                            var renameTarget = event.target.closest('[data-ml-inline-rename]');
                            if (!renameTarget || !root.contains(renameTarget)) return;
                            event.preventDefault();
                            event.stopPropagation();
                            renameMediaById(renameTarget.getAttribute('data-ml-inline-rename'));
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
                        submitFolderUpdate: submitFolderUpdate,
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
                        <input type="text" class="form-control" data-ml-new-folder maxlength="120" placeholder="{{ __('New subfolder') }}">
                        <button type="button" class="btn btn-light-primary" data-ml-create-folder title="{{ __('Create folder under current folder') }}">
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
                    <div class="imas-ml-path mb-3" data-ml-breadcrumb>
                        <nav class="imas-ml-breadcrumb" aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item active" aria-current="page">
                                    <i class="bi bi-house-door me-1"></i>{{ __('Root') }}
                                </li>
                            </ol>
                        </nav>
                    </div>
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
