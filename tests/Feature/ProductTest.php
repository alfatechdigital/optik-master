<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ProductTest extends TestCase
{
    public function test_halaman_input_produk_bisa_diakses()
    {
        // Simulasi login sebagai user (karena biasanya input produk butuh login)
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/produk/create');

        $response->assertStatus(200);
        $response->assertSee('Detail Produk'); // Memastikan ada tulisan ini di HTML
    }
}