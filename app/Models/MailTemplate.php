<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    use HasFactory;

    protected $table = "mail_template";

    protected $fillable = [
        'user_id',
        'template_name',
        'template',
    ];

    public function parse($data)
    {
        $parsed = preg_replace_callback('({{(.*?)}})', function ($matches) use ($data) 
        {
            list($shortCode, $index) = $matches;

            if (isset($data[$index])) 
            {
                return $data[$index];
            } 
            else 
            {
                // throw new Exception("Shortcode {$shortCode} not found in template id {$this->id}", 1);
            }
        }, $this->template);
        return $parsed;
    }
}
