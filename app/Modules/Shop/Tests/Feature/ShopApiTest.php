<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Models\Shop;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var User */
    protected $shopOwner;

    /** @var User */
    protected $customer;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a shop owner
        $this->shopOwner = User::factory()->create([
            'role' => 'shop_owner',
        ]);

        // Create a customer
        $this->customer = User::factory()->create([
            'role' => 'customer',
        ]);
    }

    /**
     * Test retrieving the list of shops.
     */
    public function testGetShopsList()
    {
        // Create sample shops
        Shop::factory()->count(3)->create([
            'owner_id' => $this->shopOwner->id,
        ]);

        // Test public access
        $response = $this->getJson('/api/v1/shops');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test shop creation by shop owner.
     */
    public function testShopOwnerCanCreateShop()
    {
        // Simulate logged in shop owner
        $this->actingAs($this->shopOwner);

        // Fake the storage disk
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->image('logo.jpg');
        $coverFile = UploadedFile::fake()->image('cover.jpg');

        $shopData = [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'website' => $this->faker->url,
            'logo_image' => $logoFile,
            'cover_image' => $coverFile,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/shop-owner/shops', $shopData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'latitude',
                'longitude',
                'phone',
                'email',
                'website',
                'logo_image',
                'cover_image',
                'is_active',
                'created_at',
                'updated_at',
            ]);

        // Assert files were stored
        $shop = Shop::find($response->json('id'));
        Storage::disk('public')->assertExists($shop->logo_image);
        Storage::disk('public')->assertExists($shop->cover_image);

        // Assert the shop was created with the correct owner
        $this->assertEquals($this->shopOwner->id, $shop->owner_id);
    }

    /**
     * Test shop update by shop owner.
     */
    public function testShopOwnerCanUpdateOwnShop()
    {
        // Simulate logged in shop owner
        $this->actingAs($this->shopOwner);

        // Create a shop
        $shop = Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
        ]);

        $updateData = [
            'name' => 'Updated Shop Name',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/v1/shop-owner/shops/{$shop->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Shop Name',
                'description' => 'Updated description',
            ]);

        // Assert the shop was updated in the database
        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'Updated Shop Name',
            'description' => 'Updated description',
        ]);
    }

    /**
     * Test shop owner cannot update another owner's shop.
     */
    public function testShopOwnerCannotUpdateOtherOwnerShop()
    {
        // Create another shop owner
        $anotherShopOwner = User::factory()->create([
            'role' => 'shop_owner',
        ]);

        // Create a shop owned by another owner
        $shop = Shop::factory()->create([
            'owner_id' => $anotherShopOwner->id,
        ]);

        // Simulate logged in shop owner
        $this->actingAs($this->shopOwner);

        $updateData = [
            'name' => 'Updated Shop Name',
        ];

        $response = $this->putJson("/api/v1/shop-owner/shops/{$shop->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test shop deletion by shop owner.
     */
    public function testShopOwnerCanDeleteOwnShop()
    {
        // Simulate logged in shop owner
        $this->actingAs($this->shopOwner);

        // Create a shop
        $shop = Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
        ]);

        $response = $this->deleteJson("/api/v1/shop-owner/shops/{$shop->id}");

        $response->assertStatus(200);

        // Assert the shop was deleted from the database
        $this->assertDatabaseMissing('shops', [
            'id' => $shop->id,
        ]);
    }

    /**
     * Test customers can see shop details.
     */
    public function testCustomerCanSeeShopDetails()
    {
        // Simulate logged in customer
        $this->actingAs($this->customer);

        // Create a shop
        $shop = Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/customer/shops/{$shop->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'latitude',
                'longitude',
                'phone',
                'email',
                'website',
                'logo_image',
                'cover_image',
                'is_active',
                'average_rating',
                'review_count',
                'created_at',
                'updated_at',
                'owner',
                'review_summary',
            ]);
    }

    /**
     * Test customers cannot see inactive shops.
     */
    public function testCustomerCannotSeeInactiveShops()
    {
        // Simulate logged in customer
        $this->actingAs($this->customer);

        // Create an inactive shop
        $shop = Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/v1/customer/shops/{$shop->id}");

        $response->assertStatus(404);
    }

    /**
     * Test nearby shops feature.
     */
    public function testNearbyShopsFeature()
    {
        // Create shops with different locations
        Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060, // NYC
            'is_active' => true,
        ]);

        Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
            'latitude' => 34.0522,
            'longitude' => -118.2437, // LA
            'is_active' => true,
        ]);

        Shop::factory()->create([
            'owner_id' => $this->shopOwner->id,
            'latitude' => 41.8781,
            'longitude' => -87.6298, // Chicago
            'is_active' => true,
        ]);

        // Search near NYC with small radius
        $response = $this->getJson('/api/v1/shops?latitude=40.7128&longitude=-74.0060&radius=5');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Search with larger radius
        $response = $this->getJson('/api/v1/shops?latitude=40.7128&longitude=-74.0060&radius=1000');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
