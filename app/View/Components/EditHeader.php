<?php

namespace App\View\Components;

use Illuminate\View\Component;

class EditHeader extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct( $title = null, $defaultKey = null )
    {
        $this->title = $title;
        $this->defaultKey = $defaultKey;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.edit-header')->with( [ 
            'title' => $this->title, 
            'defaultKey' => $this->defaultKey,
        ] );
    }
}
