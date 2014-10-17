# Kern Orm

**Disclaimer** - This documentation is old, and the ORM has been worked on much more than this, so this documentation shouldn't be regarded as very useful for now

The Kern Orm is an idea in the making that will allow any plain old php object to be saved to a database. I haven't fully thought through all of the details, but the main issue is that an Application model and the database are kind of two different things. So this would be an elegant way to separate application logic from the database abstraction layer. So instead of combing database models with application models, those would be separate.

My idea would be that there would be a group of interfaces like Db_serializable and so on, that allow the ORM to save, update, get, and delete plain objects.

````php

<?php

/* create a user Model */
$user = new Model\User();

/* Save the user model to the database */
Kern\Orm::save($user);

/* Update model to database */
Kern\Orm::update($user);

/* Delete model from database */
Kern\Orm::delete($user);

/* Grab model from database */
$users = Kern\Orm::get('Model\User');
````

The user model would handle all business logic and have a public api of properties which may or may not be the same as a database table. 

````php
<?php

namespace Model;

class User
{
    public $id;
    public $name;
    public $country;
    public $divisions;
}
````

There is going to be basically a bunch of schema files that represent the relationships between php classes and the database.
