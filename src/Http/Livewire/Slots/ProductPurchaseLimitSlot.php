<?php

namespace Armezit\Lunar\PurchaseLimit\Http\Livewire\Slots;

use Armezit\Lunar\PurchaseLimit\Models\PurchaseLimit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Lunar\Hub\Slots\AbstractSlot;
use Lunar\Hub\Slots\Traits\HubSlot;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductPurchaseLimitSlot extends Component implements AbstractSlot
{
    use HubSlot;

    /**
     * @var array
     */
    public $customerGroupLimits = [];

    public static function getName()
    {
        return 'hub.product.slots.product-purchase-limit-slot';
    }

    public function getSlotHandle()
    {
        return 'product-purchase-limit';
    }

    public function getSlotInitialValue()
    {
        return [
            'customerGroupLimits' => $this->customerGroupLimits,
        ];
    }

    public function getSlotPosition()
    {
        return 'bottom';
    }

    public function getSlotTitle()
    {
        return __('lunarphp-purchase-limit::slots.product.title');
    }

    public function render()
    {
        return view('lunarphp-purchase-limit::livewire.slots.product-purchase-limit');
    }

    protected function rules()
    {
        return [
            'customerGroupLimits' => [
                'nullable',
                'array',
            ],
            'customerGroupLimits.*.id' => [
                'nullable',
            ],
            'customerGroupLimits.*.customer_group_id' => [
                'nullable',
            ],
            'customerGroupLimits.*.max_quantity' => [
                'nullable',
                'required_without:customerGroupLimits.*.max_total',
                'numeric',
                'min:1',
            ],
            'customerGroupLimits.*.max_total' => [
                'nullable',
                'required_without:customerGroupLimits.*.max_quantity',
                'numeric',
                'min:1',
            ],
            'customerGroupLimits.*.period' => [
                'nullable',
                'numeric',
                'min:1',
            ],
        ];
    }

    protected function validationAttributes()
    {
        return [
            'customerGroupLimits.*.customer_group_id' => 'Customer Group',
            'customerGroupLimits.*.max_quantity' => 'Max Quantity',
            'customerGroupLimits.*.max_total' => 'Max Total',
            'customerGroupLimits.*.period' => 'Time Period',
        ];
    }

    public function mount()
    {
        $this->customerGroupLimits = $this->mapCustomerGroupLimits();
    }

    private function mapCustomerGroupLimits()
    {
        return PurchaseLimit::where([
            'product_id' => ($this->slotModel instanceof Product) ? $this->slotModel->id : 0,
            'product_variant_id' => ($this->slotModel instanceof ProductVariant) ? $this->slotModel->id : 0,
            'customer_id' => 0,
        ])
            ->get()
            ->toArray();
    }

    /**
     * Computed method to get all available customer groups.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::get();
    }

    /**
     * Returns the periods computed property.
     */
    public function getPeriodsProperty()
    {
        return [
            __('lunarphp-purchase-limit::global.per_purchase') => '',
            __('lunarphp-purchase-limit::global.daily') => 1,
            __('lunarphp-purchase-limit::global.weekly') => 7,
            __('lunarphp-purchase-limit::global.monthly') => 30,
        ];
    }

    /**
     * Method to add a customer group limit to the stack.
     */
    public function addCustomerGroupLimit()
    {
        $this->customerGroupLimits[] = new PurchaseLimit();
    }

    /**
     * Method to remove a customer group limit from the stack.
     *
     * @param  int  $index
     */
    public function removeCustomerGroupLimit(int $index)
    {
        unset($this->customerGroupLimits[$index]);
        $this->updateSlotData();
    }

    public function updated()
    {
        $this->updateSlotData();
    }

    private function updateSlotData()
    {
        try {
            $validatedData = $this->validate();
            $this->saveSlotData($validatedData);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        }
    }

    public function updateSlotModel()
    {
    }

    public function handleSlotSave($model, $data)
    {
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return $validator->getMessageBag();
        }

        $this->slotModel = $model;
        $this->saveCustomerGroupPurchaseLimits($model, $data['customerGroupLimits']);
    }

    /**
     * save customer purchase limits
     *
     * @param  Model  $model
     * @param  array  $data
     */
    public function saveCustomerGroupPurchaseLimits(Model $model, array $data)
    {
        if (empty($data)) {
            return;
        }

        $baseData = [
            'product_id' => $model instanceof Product ? $model->id : 0,
            'product_variant_id' => $model instanceof ProductVariant ? $model->id : 0,
            'customer_id' => 0,
        ];

        DB::transaction(function () use ($baseData, $data) {
            $upsert = [];
            $limitsToKeep = [];
            foreach ($data as $limit) {
                $upsert[] = array_merge($baseData, [
                    'customer_group_id' => $limit['customer_group_id'] ?? 0,
                    'max_quantity' => $limit['max_quantity'] ?? null,
                    'max_total' => $limit['max_total'] ?? null,
                    'period' => $limit['period'] ?? null,
                ]);
                $limitsToKeep[] = $limit['id'] ?? null;
            }

            PurchaseLimit::upsert(
                $upsert,
                ['product_id', 'product_variant_id', 'customer_id', 'customer_group_id']
            );

            PurchaseLimit::withoutTrashed()
                         ->whereNotIn('id', $limitsToKeep)
                         ->forceDelete();
        });
    }
}
