<?php

namespace App\Bootstrap;

use App\BootstrapInterface;
use App\Constants\Services;
use App\Constants\Types;
use App\Model\Project;
use App\Model\Ticket;
use Phalcon\Config;
use Phalcon\DiInterface;
use PhalconRest\Api;
use Schema\Definition\EnumType;
use Schema\Definition\Field;
use Schema\Definition\ModelFields\ModelField;
use Schema\Definition\ModelObjectType;
use Schema\Definition\ObjectType;
use Schema\Definition\Schema;

class SchemaBootstrap implements BootstrapInterface
{
    public function run(Api $api, DiInterface $di, Config $config)
    {
        $schema = Schema::factory()

            /**
             * Define Enum Types
             */
            ->enum(EnumType::factory(Types::PROJECT_STATE_ENUM, 'Represents the state of the project')
                ->value('OPEN', 0, 'Open')
                ->value('CLOSED', 1, 'Closed')
            )
            ->enum(EnumType::factory(Types::TICKET_STATE_ENUM, 'Represents the state of the ticket')
                ->value('NEW', 0, 'New')
                ->value('IN_PROGRESS', 1, 'In Progress')
                ->value('COMPLETED', 2, 'Completed')
            )

            /**
             * Define Object Types
             */
            ->object(ObjectType::query()
                ->field(Field::viewer())
            )

            ->object(ObjectType::viewer()
                ->field(ModelField::all(Project::class, 'allProjects', Types::connection(Types::PROJECT)))
                ->field(ModelField::find(Project::class, 'findProject', Types::PROJECT))
                ->field(ModelField::all(Ticket::class, 'allTickets', Types::connection(Types::TICKET)))
                ->field(ModelField::find(Ticket::class, 'findTicket', Types::TICKET))
            )

            ->embeddedObject(ModelObjectType::factory(Project::class, Types::PROJECT)
                ->field(Field::factory('tickets', Types::connection(Types::TICKET))
                    ->nonNull()
                )
            )

            ->embeddedObject(ModelObjectType::factory(Ticket::class, Types::TICKET)
                ->field(Field::factory('project', Types::PROJECT))
            );

        $di->setShared(Services::SCHEMA, $schema);
    }
}
