# Laravel Rights

This is very much a work in progress, but the idea is that this is a permission system that is super flexible and as much as possible based on a permission structure stored in the database.

As in, normally you can:

* *either* choose to have a very basic permission system and have it stored in the database so that you can make an admin panel in which you can manage the permissions.
* *or* choose to have a very flexible permission system, with lots of custom logic, but then you have to write it in code and therefore can not be managed in an admin panel. Or only to a very limited degree.

This project tries to make the best of both worlds as much as possible.

What I've gotten to work so far is the following:

* There are `groups`, which are stored in a tree structure, so that admins automatically inherit all the permissions of all the groups underneath.
* We have `users`, obviously. And also permissions, which are called `rights` in this project.
* Users can be assigned to any number of groups.
* Rights can be assigned to groups *and* to users.
* There are also `conditions` which can apply to `rights`. For example, the `right` to `create` a `MoneyTransfer` may have the `condition` that the `$moneyTransfer->amount < 1000` or whatever.

All of those things are defined in the database so can be easily changed through an admin panel, which I haven't made yet though.

There's already a `App\Services\Guardian` that can do the following:

* You can give it a query builder (let's say that your querying posts) and the guardian will add all the necessary constraints so that you can only get the posts that the currently logged-in user is allowed to see.
* You can give it a model that was just created or updated by the user (not yet saved though) and ask the guardian if the user is allowed to create/update this model.
* When you're asking the guardian whether a user is allowed to edit a certain model, it will take into consideration that the user may be allowed to edit some attributes, but not others.

The next thing I'm gonna work on will be using model scopes as conditions. For example, if you have this:

```php
namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    public function scopeOwnedByAuthUser(Builder $builder)
    {
        $builder->where('user_id', Auth::id());
    }
}
```

Then I want to make it possible to define a `condition` that uses that scope.

I'm leaving the following code snippet as a note for myself:

```php
use Illuminate\Support\Str;

collect(get_class_methods(App\Models\Post::class))
  ->filter(
    fn ($method) => Str::startsWith($method, 'scope')
  )
  ->map(
    fn ($method) => Str::snake(substr($method, 5), ' ')
  );

// ^ that produces ["owned by logged in user"]
```
