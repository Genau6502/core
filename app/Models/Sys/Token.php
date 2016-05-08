<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sys\Token
 *
 * @property integer $token_id
 * @property integer $related_id
 * @property string $related_type
 * @property string $type
 * @property string $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $expires_at
 * @property string $used_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Sys\Token $related
 * @property-read mixed $is_used
 * @property-read mixed $is_expired
 * @property-read mixed $display_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesOwner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesExtra
 * @property-read mixed $timeline_entries_recent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token ofType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token expired()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token notExpired()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token used()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token notUsed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token valid()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token hasCode($code)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereTokenId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereRelatedId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereRelatedType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereUsedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Token extends \App\Models\aModel {

    use SoftDeletingTrait;

    protected $table = "sys_token";
    protected $primaryKey = "token_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['token_id'];

    public function related() {
        return $this->morphTo();
    }

    public function scopeHasCode($query, $code){
        return $query->where("code", "=", $code);
    }

    public function scopeOfType($query, $type){
        return $query->where("type", "=", $type);
    }

    public function scopeExpired($query){
        return $query->where("expires_at", "<=", \Carbon\Carbon::now()->toDateTimeString());
    }

    public function scopeNotExpired($query){
        return $query->where("expires_at", ">=", \Carbon\Carbon::now()->toDateTimeString());
    }

    public function scopeUsed($query){
        return $query->whereNotNull("used_at");
    }

    public function scopeNotUsed($query){
        return $query->whereNull("used_at");
    }

    public function scopeValid($query){
        return $query->notUsed()->notExpired();
    }

    public static function generate($type, $allowDuplicates = false, $relation = null) {
        if ($allowDuplicates == false) {
            foreach ($relation->tokens()->whereType($type)->notExpired()->get() as $t) {
                $t->delete();
            }
        }

        $token = new Token;
        $token->type = $type;
        $token->expires_at = \Carbon\Carbon::now()->addDay()->toDateTimeString();
        $token->code = uniqid(uniqid());

        if ($relation != null) {
            $relation->tokens()->save($token);
        } else {
            $token->save();
        }

        return $token;
    }

    public function consume(){
        if(!$this OR $this->is_used OR $this->is_expired){
            return false;
        }

        $this->used_at = \Carbon\Carbon::now()->toDateTimeString();
        $this->save();
    }

    public function getIsUsedAttribute() {
        return ($this->attributes['used_at'] != NULL && \Carbon\Carbon::parse($this->attributes['used_at'])->isPast());
    }

    public function getIsExpiredAttribute(){
        return \Carbon\Carbon::parse($this->attributes['expires_at'])->isPast();
    }

    public function __toString(){
        return array_get($this->attributes, "code", "NoValue");
    }

    public function getDisplayValueAttribute() {

    }

}
