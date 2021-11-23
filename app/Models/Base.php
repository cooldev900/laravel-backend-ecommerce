<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * @OA\Property(property="created_at", type="string", format="date-time", description="Initial creation timestamp", readOnly="true"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp", readOnly="true"),
 * @OA\Property(property="deleted_at", type="string", format="date-time", description="Soft delete timestamp", readOnly="true"),
 * @OA\Property(
 *      property="token",
 *      type="string",
 *      description="crypted token string",
 *      example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9vbW5pbG9jYWwuY29tXC9hcGlcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNjM3NjUzODQ3LCJleHAiOjE2Mzc2NTc0NDcsIm5iZiI6MTYzNzY1Mzg0NywianRpIjoiN3M0elFjUllRckw1SFB6ZSIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.eVcdAYM1k0vRtFxRkNKC-bTOny-P27wm_Z_2lzrJ5CE",
 *      ),
 * )
 * Class BaseModel
 *
 * @package App\Models
 */

abstract class BaseModel extends Model
{
    use HasFactory;
}