<div class="shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
        <header class="flex items-center justify-between">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{ __('getcandy-purchase-limit::slots.product.heading') }}
            </h3>
        </header>

        <div class="flex items-center justify-between pt-4 border-t">
            <div>
                <strong>{{ __('getcandy-purchase-limit::slots.product.customer_groups.title') }}</strong>
                <p class="text-xs text-gray-600">
                    {{ __('getcandy-purchase-limit::slots.product.customer_groups.strapline') }}
                </p>
            </div>
            <x-hub::button :disabled="count($customerGroupLimits) >= count($this->customerGroups) + 1" wire:click.prevent="addCustomerGroupLimit" theme="gray" size="sm" type="button">
                {{ __('getcandy-purchase-limit::slots.product.customer_groups.add_group_btn') }}
            </x-hub::button>
        </div>

        <!-- customer group limits -->
        <div class="space-y-4">
            @if(count($customerGroupLimits))
                @foreach($customerGroupLimits as $index => $limit)
                    <div wire:key="limit_{{ $index }}">
                        <div class="flex items-center">
                            <div class="grid grid-cols-4 gap-4 grow">
                                <input type="hidden" value="{{ $limit->id ?? null }}"
                                       wire:model='customerGroupLimits.{{ $index }}.id'/>

                                <div class="flex flex-col space-y-2">
                                    <label class="block text-sm font-medium text-slate-700">{{ __('getcandy-purchase-limit::global.customer_group') }}</label>
                                    <x-hub::input.select
                                        wire:model='customerGroupLimits.{{ $index }}.customer_group_id'>
                                        <option value="*">{{ __('getcandy-purchase-limit::global.any') }}</option>
                                        @foreach($this->customerGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </x-hub::input.select>
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <label class="block text-sm font-medium text-slate-700">{{ __('getcandy-purchase-limit::global.max_quantity') }}</label>
                                    <x-hub::input.text
                                        id="max_quantity_field_{{ $index }}"
                                        wire:model='customerGroupLimits.{{ $index }}.max_quantity'
                                        type="number"
                                        min="1"
                                        steps="1"
                                        required
                                        onkeydown="return event.keyCode !== 190"
                                        :error="$errors->first('customerGroupLimits.'.$index.'.max_quantity')"
                                    />
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <label class="block text-sm font-medium text-slate-700">{{ __('getcandy-purchase-limit::global.max_total') }}</label>
                                    <x-hub::input.text
                                        id="max_total_field_{{ $index }}"
                                        wire:model='customerGroupLimits.{{ $index }}.max_total'
                                        type="number"
                                        min="1"
                                        steps="1"
                                        required
                                        onkeydown="return event.keyCode !== 190"
                                        :error="$errors->first('customerGroupLimits.'.$index.'.max_total')"
                                    />
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <label class="block text-sm font-medium text-slate-700">{{ __('getcandy-purchase-limit::global.time_period') }}</label>
                                    <x-hub::input.select wire:model='customerGroupLimits.{{ $index }}.period'>
                                        @foreach($this->periods as $label => $value)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-hub::input.select>
                                </div>
                            </div>
                            <div class="mr-4 rtl:mr-0 rtl:ml-4">
                                <div class="flex flex-col space-y-2">
                                    <label class="block text-sm">&nbsp;</label>
                                    <button class="text-gray-400 hover:text-red-500"
                                            wire:click.prevent="removeCustomerGroupLimit('{{ $index }}')">
                                        <x-hub::icon ref="trash" class="w-5"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @foreach($errors->get('customerGroupLimits.'.$index.'*') as $error)
                            <p class="mt-2 text-sm text-red-600">{{ \Illuminate\Support\Arr::first($error) }}</p>
                        @endforeach
                    </div>
                @endforeach
            @else
            @endif
        </div>

    </div>
</div>
