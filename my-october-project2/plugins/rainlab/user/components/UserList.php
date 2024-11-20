<?php

namespace RainLab\User\Components;

use Log;
use Flash;
use Redirect;
use RainLab\User\Models\User;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Response;

class UserList extends ComponentBase
{
    public $users;

    public function componentDetails()
    {
        return [
            'name'        => 'User List',
            'description' => 'Displays a list of registered users.'
        ];
    }

    public function onRun()
    {
        // Fetch all users from the database
        $this->users = User::all();  // Get all users

        // Log to check if users are being retrieved
        Log::info('Users fetched: ' . $this->users->count());

        // Passing users to the page
        $this->page['users'] = $this->users;
    }

    public function onDeleteUser()
    {
        $userId = post('userId');
        $user = User::find($userId);
    
        if ($user) {
            // Delete the user
            $user->delete();
    
            return [
                'message' => 'User deleted successfully.'
            ];
        }
    
        return [
            'error' => 'User not found.'
        ];
    }
    

    public function onSelectUser()
    {
        // Get the userId from the AJAX request
        $userId = post('userId');

        // Find the user by ID
        $user = User::find($userId);

        // Check if the user exists
        if ($user) {
            // Return user data as JSON
            return ['user' => $user->toArray()];
        } else {
            // Return an error message if user not found
            return ['error' => 'User not found'];
        }
    }

    public function onUpdateUser()
    {
        // Retrieve data from the form
        $userId = post('userId');
        $firstName = post('first_name');
        $lastName = post('last_name');
        $email = post('email');
        $username = post('username');
        
        // Find the user by ID
        $user = User::find($userId);
        
        if (!$user) {
            return Response::make(
                json_encode(['error' => 'User not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }
        
        // Check for unique email and username
        $existingEmail = User::where('email', $email)->where('id', '!=', $user->id)->first();
        $existingUsername = User::where('username', $username)->where('id', '!=', $user->id)->first();
        
        if ($existingEmail) {
            return Response::make(
                json_encode(['error' => 'The email has already been taken.']),
                400,
                ['Content-Type' => 'application/json']
            );
        }
        
        if ($existingUsername) {
            return Response::make(
                json_encode(['error' => 'The username has already been taken.']),
                400,
                ['Content-Type' => 'application/json']
            );
        }
        
        // Update user details
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->email = $email;
        $user->username = $username;
        $user->save();
        
        // Get fresh list of users for the partial
        $this->users = User::all();
        
        // Redirect to the '/view' page with a success message
        return redirect('/view')->with('message', 'User details updated successfully.');
    }
    


}
