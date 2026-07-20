<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Item;

class LowStockDropdown extends Component
{
    public $items;

    protected $poll = 5000; // In milliseconds

    // public function mount()
    // {
    //     $this->items = Item::selectRaw('id,item_name, part_number, quantity, (part_number - quantity) AS difference')
    //     ->whereRaw('part_number > quantity') // Only include items where part_number is greater than quantity
    //     ->get();
    //     }

    public function render()
    {
        return view('livewire.low-stock-dropdown'); // Return the view for this component
    }
}
