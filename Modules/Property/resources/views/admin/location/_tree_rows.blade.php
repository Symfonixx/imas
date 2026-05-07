@foreach($nodes as $node)
    @php
        $children = $node->relationLoaded('treeChildren') ? $node->treeChildren : collect();
    @endphp
    <tr class="location-tree-row align-middle">
        <td>
            <div class="form-check form-check-sm form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $node->id }}"/>
            </div>
        </td>
        <td class="location-tree-name-cell">
            <div class="d-flex align-items-center gap-2 min-w-0"
                 style="padding-inline-start: {{ $depth * 1.65 }}rem;">
                @switch($node->type->value)
                    @case('city')
                        <i class="bi bi-building text-primary fs-5 flex-shrink-0"></i>
                        @break
                    @case('district')
                        <i class="bi bi-signpost-2 text-info fs-5 flex-shrink-0"></i>
                        @break
                    @default
                        <i class="bi bi-pin-map text-success fs-5 flex-shrink-0"></i>
                @endswitch
                <span class="fw-semibold text-gray-800 text-truncate">{{ $node->name }}</span>
            </div>
        </td>
        <td>
            @php
                $tone = match ($node->type->value) {
                    'city' => 'primary',
                    'district' => 'info',
                    default => 'success',
                };
            @endphp
            <span class="badge badge-light-{{ $tone }} fs-7 fw-bold">{{ __($node->type->value) }}</span>
        </td>
        <td class="text-muted fs-7">{{ $node->created_at->diffForHumans() }}</td>
        <td class="text-end">
            <a href="{{ route('admin.locations.edit', $node) }}"
               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                <i class="ki-duotone ki-message-edit fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </a>
        </td>
    </tr>
    @if($children->isNotEmpty())
        @include('property::admin.location._tree_rows', ['nodes' => $children, 'depth' => $depth + 1])
    @endif
@endforeach
