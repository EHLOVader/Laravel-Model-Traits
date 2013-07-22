Laravel-Model-Traits
====================

A collection of helpfull model traits

#### Versions
The `Versions` trait handles revisions of the models current state.


#### User Assignable
The `UserAssignable` trait handles user assignments for certain actions automaticly on your database table `created_by, updated_by, deleted_by`


---

### Model Setup
```php
class TraitsModel extends Model {
	use Humweb\Traits\Versions,
		Humweb\Traits\UserAssignable;
	
	protected $table                = 'test_traits';
	protected $softDelete           = true;
	public $timestamps              = true;
	
	protected $userAssignableFields = [
	    'created' => 'created_by',
	    'updated' => 'updated_by',
	    'deleted' => 'deleted_by',
	];
}
```
