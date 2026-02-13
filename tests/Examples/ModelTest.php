<?php

namespace Teamipag\Sdk\Tests;

use DateTime;
use Teamipag\Sdk\Model\Model;
use Teamipag\Sdk\Model\Schema\Schema;
use Teamipag\Sdk\Model\Schema\SchemaBuilder;

class User extends Model
{
    protected function schema(SchemaBuilder $schema): Schema
    {
        $schema->int('id')->default(1)->nullable();
        $schema->date('created_at')->format('Y-m-d')->nullable();
        $schema->enum('groups', ['admin', 'user'])->array()->array()->nullable();
        $schema->has('child', User::class)->many()->nullable();
        return $schema->build();
    }
}

class ModelTest extends BaseTestCase
{
    public function testCanParseModel()
    {
        $model = User::parse([
            'id' => 2,
            'created_at' => '2022-08-27',
            'groups' => [['admin'], ['user']],
            'child' => [
                [
                    'id' => 99,
                    'created_at' => null,
                    'child' => [
                        [
                            'id' => 3,
                            'created_at' => new DateTime()
                        ]
                    ]
                ],
                [
                    'id' => 100,
                ]
            ]
        ]);

        dd($model, $model->jsonSerialize());
    }
}
