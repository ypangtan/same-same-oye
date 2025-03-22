<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;
use Carbon\Carbon;

class VendingMachine extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'outlet_id',
        'title',
        'description',
        'quick_description',
        'code',
        'image',
        'latitude',
        'longitude',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'opening_hour',
        'closing_hour',
        'navigation_links',
        'status',
        'api_key',
    ];

    public function galleries()
    {
        return $this->hasMany(VendingMachineGallery::class, 'vending_machine_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function stocks()
    {
        return $this->hasMany(VendingMachineStock::class, 'vending_machine_id');
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }
    public function getOperationalHourAttribute()
    {
        // Extract and format opening and closing hours
        $openingHour = $this->attributes['opening_hour'] 
            ? Carbon::parse($this->attributes['opening_hour'])->format('g:ia') 
            : null;
    
        $closingHour = $this->attributes['closing_hour'] 
            ? Carbon::parse($this->attributes['closing_hour'])->format('g:ia') 
            : null;
       
        // Get current time as a Carbon instance
        $currentTime = now()->addHours(8);

        $start = Carbon::parse($openingHour);
        $end = Carbon::parse($closingHour);
        $current = Carbon::parse($currentTime);

        // Handle cases where the end time crosses midnight
        if ($end->lessThan($start)) {
            $end->addDay(); // Add a day to the end time to handle the next day
        }

        // Determine if the current time is within the operational range
        $isInOperation = $start && $end &&
            $currentTime->between($start, $end);

        // Generate operational hours string
        $operationString = $openingHour && $closingHour
            ? "{$openingHour} - {$closingHour}"
            : "Hours not set";
    
        // Append status
        $statusString = $isInOperation ? 'In Operation' : 'Closed now, redeem later.';
    
        return "{$operationString}, {$statusString}";
    }
    
    public function getFormattedOpeningHourAttribute(){
        $openingHour = $this->attributes['opening_hour'] 
            ? Carbon::parse($this->attributes['opening_hour'])->format('g:ia') 
            : null;

        return $openingHour;
    }

    public function getFormattedClosingHourAttribute(){
        $openingHour = $this->attributes['closing_hour'] 
            ? Carbon::parse($this->attributes['closing_hour'])->format('g:ia') 
            : null;

        return $openingHour;
    }

    public function getStatusLabelAttribute(){
        $statusLabel =  '';

        switch ($this->attributes['status']) {
            case 10:
                $statusLabel =  'Active';
                break;
            case 20:
                $statusLabel =  'Offline';
                break;
            case 21:
                $statusLabel =  'Maintenance Required';
                break;
            default:
                # code...
                break;
        }

        return $statusLabel;
    }

    public $translatable = [ 'name', 'description', 'quick_description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'outlet_id',
        'title',
        'description',
        'quick_description',
        'code',
        'image',
        'latitude',
        'longitude',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'opening_hour',
        'closing_hour',
        'navigation_links',
        'status',
        'api_key',
    ];

    protected static $logName = 'vending_machines';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vending machine";
    }
}
