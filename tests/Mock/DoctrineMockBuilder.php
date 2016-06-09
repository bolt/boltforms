<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests\Mock;

class DoctrineMockBuilder extends \Bolt\Tests\Mocks\DoctrineMockBuilder
{
    /**
     * @return \Doctrine\DBAL\Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getConnectionMock()
    {
        $mock = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'beginTransaction',
                    'commit',
                    'rollback',
                    'prepare',
                    'query',
                    'executeQuery',
                    'executeUpdate',
                    'getDatabasePlatform',
                    'createQueryBuilder',
                    'connect',
                    'insert',
                    'getSchemaManager',
                ]
            )
            ->getMock();

        $mock->expects($this->any())
                ->method('prepare')
                ->will($this->returnValue($this->getStatementMock()));

        $mock->expects($this->any())
                ->method('query')
                ->will($this->returnValue($this->getStatementMock()));

        $mock->expects($this->any())
                ->method('createQueryBuilder')
                ->will($this->returnValue($this->getQueryBuilderMock($mock)));

        $mock->expects($this->any())
                ->method('getDatabasePlatform')
                ->will($this->returnValue($this->getDatabasePlatformMock()));

        return $mock;
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getSchemaManagerMock($db, $tablesExist = true, $columns = [])
    {
        $mock = $this->getMockForAbstractClass(
            'Doctrine\DBAL\Schema\AbstractSchemaManager',
            [$db],
            '',
            true,
            true,
            true,
            [
                'listTableColumns',
                'tablesExist',
            ],
            false
        );

        foreach ($columns as $column) {
            $columnMock[] = $this->getColumnMock($column);
        }

        $mock->expects($this->any())
            ->method('listTableColumns')
            ->will($this->returnValue($columnMock));

        $mock->expects($this->any())
            ->method('tablesExist')
            ->will($this->returnValue($tablesExist));

        return $mock;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column
     */
    public function getColumnMock($name)
    {
        $mock = $this->getMockBuilder('Doctrine\DBAL\Schema\Column')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getName',
                ]
            )
            ->getMock();

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $mock;
    }
}
