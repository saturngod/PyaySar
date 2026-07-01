<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CustomerService
{
    /**
     * @return array<int, Customer>
     */
    public function list(User $user): array
    {
        return $user->customers()
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data, ?UploadedFile $avatar = null): Customer
    {
        if ($avatar) {
            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        return $user->customers()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data, ?UploadedFile $avatar = null): Customer
    {
        if ($avatar) {
            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }

            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        if ($customer->avatar) {
            Storage::disk('public')->delete($customer->avatar);
        }

        $customer->delete();
    }
}
