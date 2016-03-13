<?php

    namespace Dez\Dev\Generator;

    use Dez\Cli\Cli;
    use Dez\Config\Config;
    use Dez\Db\Connection;
    use Dez\Dev\RuntimeErrorException;
    use Dez\View\View;

    class OrmEntity {

        protected $connection;

        protected $view;

        protected $cli;

        protected $config;

        /**
         *
         */
        public function __construct() {

        }

        /**
         * @param $message
         * @return $this
         */
        public function writeln( $message = '' ) {
            $output = $this->getCli()->getOutput();
            $output->writeln( $message );
            return $output;
        }

        /**
         * @throws \Dez\View\Exception
         */
        public function execute() {

            $this->writeln( '  [info]Configure view[/info]' );
            $view       = $this->getView();

            $table              = $this->getConfig()->get( 'tableName' );
            $generatedName      = 'Entity_' . md5( $table );

            $view->setViewDirectory( __DIR__ . '/Template' );
            $view->set( 'generator', $this );
            $view->set( 'columns', $this->getColumns() );
            $view->set( 'table', $this->getConfig()->get( 'tableName' ) );
            $view->set( 'namespace', $this->getConfig()->get( 'namespace' ) );
            $view->set( 'entityName', $generatedName );

            $generatedContent       = $view->fetch( 'GeneratedOrmEntity.html' );
            $userContent            = $view->fetch( 'OrmEntity.html' );
            $this->writeln( '  [info]Render entity content[/info]' );

            $workDirectory      = $this->getConfig()->get( 'currentDirectory' );
            $entityDirectory    = $this->getConfig()->get( 'entityDirectory' );

            $entityDirectory    = "$workDirectory/$entityDirectory";
            $generatedEntity    = "$entityDirectory/GeneratedEntity";

            if( ! is_dir( $generatedEntity ) ) {
                $this->writeln( '  [info]Creating directory for entity: '. $generatedEntity .'[/info]' );
                mkdir( $generatedEntity, 0777, true );
            }

            $entityFile         = $this->camelize( $table );

            $userFile           = "$entityDirectory/$entityFile.php";
            $generatedFile      = "$generatedEntity/$generatedName.php";

            $this->writeln( '  [info]Updated entity '. $generatedFile .'[/info]' );
            file_put_contents( $generatedFile, $generatedContent );

            if( ! file_exists( $userFile ) ) {
                $this->writeln( '  [info]Create user file '. $userFile .'[/info]' );
                file_put_contents( $userFile, $userContent );
            }

            return $this;
        }

        /**
         * @return \Generator
         * @throws \Dez\Db\Exception
         */
        public function getColumns() {
            $tableName  = $this->getConfig()->get( 'tableName' );
            $this->writeln( '  [info]Prepare columns for "'. $tableName .'"[/info]' );

            $query = 'show columns from `'. $tableName .'`';
            $stmt   = $this->getConnection()->query( $query );

            $columns    = [];

            foreach( $stmt->fetchAll( Connection::FETCH_OBJ ) as $row ) {
                if( strpos( strtolower( $row->Key ), 'pri' ) !== false ) {
                    continue;
                } else {
                    $columns[]  = $row->Field;
                }
            }

            return $columns;
        }

        /**
         * @param $string
         * @return string
         */
        public function camelize( $string ) {
            return implode( '', array_map( 'ucfirst', explode( '_', trim( $string ) ) ) );
        }

        /**
         * @return bool|string
         */
        public function currectDate() {
            return date( 'Y-m-d H:i:s' );
        }

        /**
         * @return Connection
         */
        public function getConnection() {
            return $this->connection;
        }

        /**
         * @param Connection $connection
         * @return $this
         */
        public function setConnection( Connection $connection ) {
            $this->connection = $connection;
            return $this;
        }

        /**
         * @return View
         */
        public function getView() {
            return $this->view;
        }

        /**
         * @param View $view
         * @return $this
         */
        public function setView( View $view ) {
            $this->view = $view;
            return $this;
        }

        /**
         * @return Cli
         */
        public function getCli() {
            return $this->cli;
        }

        /**
         * @param Cli $cli
         * @return $this
         */
        public function setCli( Cli $cli ) {
            $this->cli = $cli;
            $cli->getOutput()->writeln( '  [success]'. __CLASS__ .' running[/success]' );
            return $this;
        }

        /**
         * @return Config
         */
        public function getConfig() {
            return $this->config;
        }

        /**
         * @param Config $config
         * @return $this
         */
        public function setConfig( Config $config ) {
            $this->config = $config;
            return $this;
        }

    }