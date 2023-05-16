<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RaspberryDeviceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class RaspberryDeviceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RaspberryDeviceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\RaspberryDevice::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/raspberry-device');
        CRUD::setEntityNameStrings('raspberry device', 'raspberry devices');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('model');
        CRUD::column('last_ip');
        CRUD::column('email_notification');
        CRUD::column('gpio_settings');
        CRUD::column('gpio_0_status');
        CRUD::column('gpio_1_status');
        CRUD::column('gpio_2_status');
        CRUD::column('gpio_3_status');
        CRUD::column('gpio_4_status');
        CRUD::column('gpio_5_status');
        CRUD::column('gpio_6_status');
        CRUD::column('gpio_7_status');
        CRUD::column('gpio_8_status');
        CRUD::column('gpio_9_status');
        CRUD::column('gpio_10_status');
        CRUD::column('gpio_11_status');
        CRUD::column('gpio_12_status');
        CRUD::column('gpio_13_status');
        CRUD::column('gpio_14_status');
        CRUD::column('gpio_15_status');
        CRUD::column('gpio_16_status');
        CRUD::column('gpio_17_status');
        CRUD::column('gpio_18_status');
        CRUD::column('gpio_19_status');
        CRUD::column('gpio_20_status');
        CRUD::column('gpio_21_status');
        CRUD::column('gpio_22_status');
        CRUD::column('gpio_23_status');
        CRUD::column('gpio_24_status');
        CRUD::column('gpio_25_status');
        CRUD::column('gpio_26_status');
        CRUD::column('gpio_27_status');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(RaspberryDeviceRequest::class);

        CRUD::field('name');
        CRUD::field('model');
        CRUD::field('last_ip');
        CRUD::field('email_notification');
        CRUD::field('gpio_settings');

        for ($x = 0; $x <= 27; $x++) {
            
            CRUD::addField([
                'name'    => "gpio_{$x}_status",
                'type'    => 'enum',/*
                'options' => [
                    'available',
                    'in-use',
                    'out-of-service'
                ]*/
            ]);
        }
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
