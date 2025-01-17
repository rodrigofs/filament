@php
    use Filament\Forms\Components\Actions\Action;

    $containers = $getChildComponentContainers();

    $addAction = $getAction($getAddActionName());
    $addBetweenAction = $getAction($getAddBetweenActionName());
    $cloneAction = $getAction($getCloneActionName());
    $collapseAllAction = $getAction($getCollapseAllActionName());
    $expandAllAction = $getAction($getExpandAllActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isCollapsible = $isCollapsible();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{}"
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-repeater grid gap-y-4'])
        }}
    >
        @if ($isCollapsible && ($collapseAllAction->isVisible() || $expandAllAction->isVisible()))
            <div
                @class([
                    'flex gap-x-3',
                    'hidden' => count($containers) < 2,
                ])
            >
                @if ($collapseAllAction->isVisible())
                    <span
                        x-on:click="$dispatch('repeater-collapse', '{{ $statePath }}')"
                    >
                        {{ $collapseAllAction }}
                    </span>
                @endif

                @if ($expandAllAction->isVisible())
                    <span
                        x-on:click="$dispatch('repeater-expand', '{{ $statePath }}')"
                    >
                        {{ $expandAllAction }}
                    </span>
                @endif
            </div>
        @endif

        @if (count($containers))
            <ul>
                <x-filament::grid
                    :default="$getGridColumns('default')"
                    :sm="$getGridColumns('sm')"
                    :md="$getGridColumns('md')"
                    :lg="$getGridColumns('lg')"
                    :xl="$getGridColumns('xl')"
                    :two-xl="$getGridColumns('2xl')"
                    :wire:end.stop="'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })'"
                    x-sortable
                    :data-sortable-animation-duration="$getReorderAnimationDuration()"
                    class="items-start gap-4"
                >
                    @foreach ($containers as $uuid => $item)
                        @php
                            $itemLabel = $getItemLabel($uuid);
                            $visibleExtraItemActions = array_filter(
                                $extraItemActions,
                                fn (Action $action): bool => $action(['item' => $uuid])->isVisible(),
                            );
                            $itemHasToolbar = $isReorderableWithDragAndDrop || $isReorderableWithButtons || filled($itemLabel) || $isCloneable || $isDeletable || $isCollapsible || $visibleExtraItemActions;
                        @endphp

                        <li
                            wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item"
                            x-data="{
                                isCollapsed: @js($isCollapsed($item)),
                            }"
                            x-on:expand="isCollapsed = false"
                            x-on:repeater-expand.window="$event.detail === '{{ $statePath }}' && (isCollapsed = false)"
                            x-on:repeater-collapse.window="$event.detail === '{{ $statePath }}' && (isCollapsed = true)"
                            x-sortable-item="{{ $uuid }}"
                            class="fi-fo-repeater-item divide-y divide-gray-100 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-white/5 dark:ring-white/10"
                            x-bind:class="{ 'fi-collapsed overflow-hidden': isCollapsed }"
                        >
                            @if ($itemHasToolbar)
                                <div
                                    @if ($isCollapsible)
                                        x-on:click.stop="isCollapsed = !isCollapsed"
                                    @endif
                                    @class([
                                        'fi-fo-repeater-item-header flex items-center gap-x-3 overflow-hidden px-4 py-3',
                                        'cursor-pointer select-none' => $isCollapsible,
                                    ])
                                >
                                    @if ($isReorderableWithDragAndDrop || $isReorderableWithButtons)
                                        <ul class="flex items-center gap-x-3">
                                            @if ($isReorderableWithDragAndDrop)
                                                <li
                                                    x-sortable-handle
                                                    x-on:click.stop
                                                >
                                                    {{ $reorderAction }}
                                                </li>
                                            @endif

                                            @if ($isReorderableWithButtons)
                                                <li
                                                    x-on:click.stop
                                                    class="flex items-center justify-center"
                                                >
                                                    {{ $moveUpAction(['item' => $uuid])->disabled($loop->first) }}
                                                </li>

                                                <li
                                                    x-on:click.stop
                                                    class="flex items-center justify-center"
                                                >
                                                    {{ $moveDownAction(['item' => $uuid])->disabled($loop->last) }}
                                                </li>
                                            @endif
                                        </ul>
                                    @endif

                                    @if (filled($itemLabel))
                                        <h4
                                            @class([
                                                'text-sm font-medium text-gray-950 dark:text-white',
                                                'truncate' => $isItemLabelTruncated(),
                                            ])
                                        >
                                            {{ $itemLabel }}
                                        </h4>
                                    @endif

                                    @if ($isCloneable || $isDeletable || $isCollapsible || $visibleExtraItemActions)
                                        <ul
                                            class="ms-auto flex items-center gap-x-3"
                                        >
                                            @foreach ($visibleExtraItemActions as $extraItemAction)
                                                <li x-on:click.stop>
                                                    {{ $extraItemAction(['item' => $uuid]) }}
                                                </li>
                                            @endforeach

                                            @if ($isCloneable)
                                                <li x-on:click.stop>
                                                    {{ $cloneAction(['item' => $uuid]) }}
                                                </li>
                                            @endif

                                            @if ($isDeletable)
                                                <li x-on:click.stop>
                                                    {{ $deleteAction(['item' => $uuid]) }}
                                                </li>
                                            @endif

                                            @if ($isCollapsible)
                                                <li
                                                    class="relative transition"
                                                    x-on:click.stop="isCollapsed = !isCollapsed"
                                                    x-bind:class="{ '-rotate-180': isCollapsed }"
                                                >
                                                    <div
                                                        class="transition"
                                                        x-bind:class="{ 'opacity-0 pointer-events-none': isCollapsed }"
                                                    >
                                                        {{ $getAction('collapse') }}
                                                    </div>

                                                    <div
                                                        class="absolute inset-0 rotate-180 transition"
                                                        x-bind:class="{ 'opacity-0 pointer-events-none': ! isCollapsed }"
                                                    >
                                                        {{ $getAction('expand') }}
                                                    </div>
                                                </li>
                                            @endif
                                        </ul>
                                    @endif
                                </div>
                            @endif

                            <div
                                x-show="! isCollapsed"
                                class="fi-fo-repeater-item-content p-4"
                            >
                                {{ $item }}
                            </div>
                        </li>

                        @if (! $loop->last)
                            @if ($isAddable && $addBetweenAction->isVisible())
                                <li class="flex w-full justify-center">
                                    <div
                                        class="fi-fo-repeater-add-between-action-ctn rounded-lg bg-white dark:bg-gray-900"
                                    >
                                        {{ $addBetweenAction(['afterItem' => $uuid]) }}
                                    </div>
                                </li>
                            @elseif (filled($labelBetweenItems = $getLabelBetweenItems()))
                                <li
                                    class="relative border-t border-gray-200 dark:border-white/10"
                                >
                                    <span
                                        class="absolute -top-3 left-3 px-1 text-sm font-medium"
                                    >
                                        {{ $labelBetweenItems }}
                                    </span>
                                </li>
                            @endif
                        @endif
                    @endforeach
                </x-filament::grid>
            </ul>
        @endif

        @if ($isAddable && $addAction->isVisible())
            <div class="flex justify-center">
                {{ $addAction }}
            </div>
        @endif
    </div>
</x-dynamic-component>
