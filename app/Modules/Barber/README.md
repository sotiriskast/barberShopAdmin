// Create a test barber
$barber = Barber::factory()->create();

// Create an experienced barber
$experiencedBarber = Barber::factory()->experienced()->create();

// Create a junior barber
$juniorBarber = Barber::factory()->beginner()->create();
```

## Event Handling Examples

```php
// Triggering the BarberServiceUpdated event
public function updateBarberServices(Barber $barber, array $serviceIds)
{
    // Update barber services
    $barber->services()->sync($serviceIds);
    
    // Trigger event
    event(new BarberServiceUpdated($barber, $serviceIds));
    
    return true;
}

// Triggering the BarberTimeOffCreated event
public function addTimeOff(array $data)
{
    $timeOff = TimeOff::create($data);
    
    // Trigger event
    event(new BarberTimeOffCreated($timeOff));
    
    return $timeOff;
}
```

## Advanced API Usage Examples

### Filtering Barbers by Criteria

```php
// Client-side API call
const response = await axios.get('/api/barber/list', {
  params: {
    shop_id: 1,
    service_id: 5,
    min_experience: 2,
    available_on_date: '2025-04# Barber Module

This module provides all the functionality needed to manage barbers, their services, working hours, time off periods, and availability within the barber shop booking platform.

## Features

- Barber profile management
- Service selection and price overrides
- Working hours configuration
- Time off scheduling
- Availability calculation and time slot management
- Role-based access control
- Filtering barbers by various criteria
- Event-driven appointment conflict handling

## Directory Structure

```
app/Modules/Barber/
├── Controllers/
│   └── BarberController.php
├── Models/
│   ├── Barber.php
│   ├── WorkingHour.php
│   └── TimeOff.php
├── Repositories/
│   ├── Interfaces/
│   │   └── BarberRepositoryInterface.php
│   └── Eloquent/
│       └── BarberRepository.php
├── Services/
│   ├── BarberService.php
│   └── AvailabilityService.php
├── Resources/
│   ├── BarberResource.php
│   ├── BarberCollection.php
│   ├── WorkingHourResource.php
│   ├── TimeOffResource.php
│   ├── ServiceResource.php
│   └── AvailabilityResource.php
├── Requests/
│   ├── CreateBarberRequest.php
│   ├── UpdateBarberRequest.php
│   ├── UpdateWorkingHoursRequest.php
│   ├── AddTimeOffRequest.php
│   ├── UpdateServicesRequest.php
│   ├── FilterBarbersRequest.php
│   ├── GetBarberAvailabilityRequest.php
│   └── CheckTimeSlotRequest.php
├── Events/
│   ├── BarberServiceUpdated.php
│   └── BarberTimeOffCreated.php
├── Listeners/
│   ├── NotifyAppointmentsOnServiceChange.php
│   └── CheckAppointmentConflicts.php
├── Middleware/
│   └── BarberRoleMiddleware.php
├── Providers/
│   └── BarberServiceProvider.php
├── config/
│   └── barber.php
├── database/
│   ├── migrations/
│   │   ├── create_barbers_table.php
│   │   ├── create_working_hours_table.php
│   │   ├── create_time_off_table.php
│   │   └── create_barber_services_table.php
│   └── factories/
│       └── BarberFactory.php
├── tests/
│   ├── Unit/
│   │   └── AvailabilityServiceTest.php
│   └── Feature/
└── routes/
└── api.php
```

## Installation

1. Ensure the module is loaded in your `config/app.php` providers array:

```php
'providers' => [
    // Other Service Providers...
    App\Modules\Barber\Providers\BarberServiceProvider::class,
],
```

2. Run migrations to create the necessary tables:

```bash
php artisan migrate
```

3. Publish the configuration (optional):

```bash
php artisan vendor:publish --tag=barber-config
```


### Public API Endpoints

```
GET  /api/v1/barber/list                 # Get filtered list of barbers
GET  /api/v1/barber/{id}/availability    # Get barber availability for a specific date and service
POST /api/v1/barber/availability/check-slot  # Check if a specific time slot is available
```

### Admin API Endpoints

```
POST /api/v1/barber                      # Create a new barber
```

### Barber API Endpoints

```
GET  /api/v1/barber/profile              # Get barber profile
PUT  /api/v1/barber/profile              # Update barber profile
GET  /api/v1/barber/services             # Get barber services
PUT  /api/v1/barber/services             # Update barber services
GET  /api/v1/barber/working-hours        # Get working hours
PUT  /api/v1/barber/working-hours        # Update working hours
POST /api/v1/barber/time-off             # Add time off
GET  /api/v1/barber/time-off             # List time off periods
DELETE /api/v1/barber/time-off/{id}      # Remove time off
GET  /api/v1/barber/availability         # Get available time slots
POST /api/v1/barber/availability/check   # Check if a time slot is available
```

## Events and Listeners

The module includes events and listeners to handle various scenarios:

### BarberServiceUpdated Event

Triggered when a barber updates their available services.

Listeners:
- `NotifyAppointmentsOnServiceChange`: Notifies customers with upcoming appointments that were booked for services that are no longer offered.

### BarberTimeOffCreated Event

Triggered when a barber adds a new time off period.

Listeners:
- `CheckAppointmentConflicts`: Checks for appointment conflicts with the new time off period and notifies both barbers and customers.

## Implementation Details

### Availability Calculation

The availability service calculates available time slots based on:

1. Barber's working hours for the specified day
2. Existing appointments
3. Time off periods
4. Service duration

The algorithm ensures no overlapping appointments and prevents booking outside of working hours or during time off periods.

### Filtering Barbers

The barber repository provides advanced filtering capabilities:

- Filter by shop
- Filter by service
- Filter by experience level
- Filter by availability on a specific date
- Search by name or title
- Sort by various criteria (name, experience, rating, popularity)

## Usage Examples

### Get Barber Profile

```php
// Controller example
public function getBarberInfo()
{
    $barberService = app(BarberService::class);
    $barber = $barberService->getBarberProfile(auth()->id());
    
    return new BarberResource($barber);
}
```

### Check Availability

```php
// Service example
$availabilityService = app(AvailabilityService::class);
$timeSlots = $availabilityService->getAvailableTimeSlots(
    $barberId,
    '2025-03-15', // Date
    $serviceId     // Optional service ID for duration calculation
);
```

### Update Working Hours

```php
// Service example
$barberService = app(BarberService::class);
$workingHours = [
    ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false],
    ['day_of_week' => 2, 'start_time' => '09:00', 'end_time' => '17:00', 'is_day_off' => false],
    // Add entries for all days of the week
];

$barberService->updateWorkingHours($barberId, $workingHours);
```

## Configuration

The module includes configuration options in `config/barber.php`:

- Availability settings (slot intervals, booking window)
- Scheduling settings (buffer time, overlapping appointments)
- Time off settings (duration limits)
- Default working hours

## Testing

The module includes factories for creating test data:

```php
// Create a test barber
$barber = Barber::factory()->create();

// Create an experienced barber
$experiencedBarber = Barber::factory()->experienced()->create();

// Create a junior barber
$juniorBarber = Barber::factory()->beginner()->create();
```

## License

This module is part of the Barber Shop Booking Platform and is licensed under the same terms as the main application.
