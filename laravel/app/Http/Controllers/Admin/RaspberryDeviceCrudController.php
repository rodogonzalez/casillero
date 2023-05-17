<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RaspberryDeviceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

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

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    // if you just want to show the same columns as inside ListOperation
    protected function setupShowOperation()
    {
        if (request()->input('reset')){            
            $currentEntry= $this->crud->getCurrentEntry();
            for ($x = 0; $x <= 27; $x++) {
                $currentEntry->{"gpio_{$x}_status"}= 'available';
                $currentEntry->save();
            }
        }
        $this->crud->setOperationSetting('tabsEnabled', true);
        $this->crud->addColumn([
            'name' => 'name',
            'tab'  => 'General',
        ]);
        $this->crud->addColumn([
            'name' => 'model',
            'tab'  => 'General',
        ]);
        $this->crud->addColumn([
            'name' => 'last_ip',
            'tab'  => 'General',
        ]);

        $options = new QROptions(
            [
                'eccLevel'   => QRCode::ECC_L,
                'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                'version'    => 5,
            ]
        );

        $usr_link = env('APP_URL') . '/request-locker/' . $this->crud->getCurrentEntry()->id;

        $qrcode = (new QRCode($options))->render($usr_link);

        $this->crud->addColumn([  // CustomHTML
                                   'name'  => 'separator',
                                   'label' => 'Management Link',
                                   'type'  => 'custom_html',
                                   'value' => '<img src="' . $qrcode . '"><br><a href="' . $usr_link . '">User Managment</a>',
                                   'tab'   => 'General'
                               ]);

        for ($x = 0; $x <= 27; $x++) {
            $this->crud->addColumn([
                'name' => "gpio_{$x}_status",
                'type' => 'enum',
                'tab'  => 'GPIO',
            ]);
        }

        $this->crud->addButtonFromModelFunction('line', 'reset_gpios', 'reset_gpios', 'begining');
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
        //CRUD::field('gpio_settings');

        for ($x = 0; $x <= 27; $x++) {
            CRUD::addField([
                'name' => "gpio_{$x}_status",
                'type' => 'enum',  /*
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
