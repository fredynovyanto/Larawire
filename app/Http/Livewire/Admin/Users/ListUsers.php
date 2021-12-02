<?php

namespace App\Http\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ListUsers extends Component
{
    // public $name;
    // public $email;
    // public $password;
    // public $password_confirm;
    public $state= [];
    public $user;
    public $showEditModal = false;
    public $userIdBeingRemoved = null;

    private function resetInputFields()
    {
        $this->state = [];
    }

    public function render()
    {
        $users = User::latest()->paginate(10);
        return view('livewire.admin.users.list-users', compact('users'));
    }

    public function create()
    {
        $this->showEditModal = false;
        $this->dispatchBrowserEvent('show-form');
        $this->resetInputFields();
    }

    public function store()
    {
        $validateData = Validator::make($this->state, [
            'name' => 'required',
            'email'=> 'required|email|unique:users',
            'password' => 'required|confirmed'
        ])->validate();
        $validateData['password'] = bcrypt($validateData['password']);
        User::create($validateData);
        
        // session()->flash('message', 'User added successfully!');

        $this->dispatchBrowserEvent('hide-form', ['message' => 'User added successfully!']);
        $this->resetInputFields();
    }
    
    public function edit(User $user)
    {
        $this->user = $user;
        $this->showEditModal = true;
        $this->dispatchBrowserEvent('show-form');
        $this->state = $user->toArray();
    }

    public function update()
    {
        $validateData = Validator::make($this->state, [
            'name' => 'required',
            'email'=> 'required|email|unique:users,email,'.$this->user->id,
            'password' => 'sometimes|confirmed'
        ])->validate();
        if(!empty($validateData['password'])){
            $validateData['password'] = bcrypt($validateData['password']);
        }
        $this->user->update($validateData);
        
        // session()->flash('message', 'User added successfully!');

        $this->dispatchBrowserEvent('hide-form', ['message' => 'User update successfully!']);
        $this->resetInputFields();
    }

    public function userId($id)
    {
        $this->userIdBeingRemoved = $id;
        $this->dispatchBrowserEvent('delete-modal');
    }

    public function destroy()
    {
        $user = User::findOrFail($this->userIdBeingRemoved);
        $user->delete();
        $this->dispatchBrowserEvent('hide-modal', ['message' => 'User delete successfully!']);
    }
}
