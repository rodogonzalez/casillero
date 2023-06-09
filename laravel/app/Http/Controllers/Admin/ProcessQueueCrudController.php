<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProcessQueueRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProcessQueueCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProcessQueueCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ProcessQueue::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/process-queue');
        CRUD::setEntityNameStrings('process queue', 'process queues');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('raspberry_device_id');

        CRUD::column('gpio_port');
        CRUD::column('command');

        CRUD::column('executed');

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
        CRUD::setValidation(ProcessQueueRequest::class);

        CRUD::addField(
            [                                                 // Select
                'label'     => 'Device',
                'type'      => 'select',
                'name'      => 'raspberry_device_id',         // the db column for the foreign key
                'model'     => 'App\Models\RaspberryDevice',  // related model
                'attribute' => 'name',                        // foreign key attribute that is shown to user
                                                              // optional - force the related options to be a custom query, instead of all();
                'options'   => (function ($query) {
                    return $query->orderBy('id', 'ASC')->get();
                }),
            ]
        );
        CRUD::addField([
            'name' => 'command',
            'type' => 'enum',
        ]);
        CRUD::field('executed');

        CRUD::addField(
            [                              // radio
                'name'    => 'gpio_port',  // the name of the db column
                'label'   => 'Port',       // the input label
                'type'    => 'radio',
                'options' => [
                    // the key will be stored in the db, the value will be shown as label;
                    '4' => '4', 
                    '9' => '9', 
                    '10' => '10', 
                    '11' => '11', 
                    '13' => '13', 
                    '16' => '16', 
                    '20' => '20', 
                    '21' => '21', 
                    '23' => '23', 
                    '24' => '24', 
                    '26' => '26', 
                    '27' => '27'
                ],
                // optional
                'inline' => true,          // show the radios all on the same line?
            ]
        );
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
