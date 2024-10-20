<?php
namespace App\Filters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Str;

abstract class QueryFilter
{
    protected $request;
    protected $builder;
    protected $delimiter = ',';
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->filters() as $name => $value) {
            if (method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }
        return $this->builder;
    }
    public function filters()
    {
        return $this->request->all();
    }
    protected function paramToArray($param){
        return Str::contains($param, $this->delimiter) ? explode($this->delimiter, $param) : [$param];
    }
}
