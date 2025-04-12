<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct( $id, $title, $slot = null )
    {
        $this->id = $id;
        $this->title = $title;
        $this->slot = $slot;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form-card')->with( [ 
            'id' => $this->id, 
            'title' => $this->title, 
            'slot' => $this->slot 
        ] );
    }
}
