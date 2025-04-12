<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InputField extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct( $id, $label, $type, $value = null )
    {
        $this->id = $id;
        $this->label = $label;
        $this->value = $value;
        $this->type = $type;
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.input-field')->with( [ 
            'label' => $this->label, 
            'id' => $this->id, 
            'type' => $this->type,
            'value' => $this->value,
        ] );
    }
}
