<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\RightCondition;
use App\Models\User;
use App\Models\Group;
use App\Models\Right;
use Database\Factories\RightFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Group::create([
            'slug' => 'admins',
            'name' => 'Admins',
            'description' => 'Admins can do everything',
            'children' => [
                [
                    'slug' => 'moderators',
                    'name' => 'Moderators',
                    'description' => 'Moderators can do a lot',
                    'children' => [
                        [
                            'slug' => 'users',
                            'name' => 'Users',
                            'description' => 'Users can do some things',
                        ]
                    ],
                ],
                [
                    'slug' => 'managers',
                    'name' => 'Managers',
                    'description' => 'Bla bla bla',
                    'children' => [
                        [
                            'slug' => 'employees',
                            'name' => 'Employees',
                            'description' => 'Yada yada yada',
                        ]
                    ],
                ]
            ],
        ]);

        /** @var User[] $users */
        $users = User::factory(20)->create();
        $rights = Right::factory(35)->create();

        $groups = Group::all();

        foreach ($rights as $right) {
            $model = $right->model;
            $fields = collect(RightFactory::FIELDS[$model]);

            RightCondition::factory(mt_rand(0, 3))
                ->state(function() use ($right, $fields) {
                    return [
                        'right_id' => $right->id,
                        'field' => $fields->random()
                    ];
                })->create();
        }

        foreach ($users as $user) {
            Post::factory(mt_rand(1, 5))->create(['user_id' => $user->id]);
            $user->groups()->attach($groups->random(mt_rand(1, 2)));
            $user->rights()->attach($rights->random(mt_rand(1,6)));
        }

        foreach ($groups as $group) {
            $group->rights()->attach($rights->random(mt_rand(1,6)));
        }
    }
}
