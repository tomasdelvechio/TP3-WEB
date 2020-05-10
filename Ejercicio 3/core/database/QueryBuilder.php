<?php

namespace App\Core\Database;

use PDO;
use Exception;

class QueryBuilder
{
    /**
     * The PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Create a new QueryBuilder instance.
     *
     * @param PDO $pdo
     */
    public function __construct($pdo, $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = ($logger) ? $logger : null;
    }

    /**
     * Select all records from a database table.
     *
     * @param string $table
     */
    public function selectAll($table)
    {
        $statement = $this->pdo->prepare("select * from {$table}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_CLASS);
    }

    public function selectId($id,$table)
    {
        $statement = $this->pdo->prepare("select * from {$table} where id={$id}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_CLASS);
    }

    /**
     * Insert a record into a table.
     *
     * @param  string $table
     * @param  array  $parameters
     */
    public function insert($table, $parameters)
    {
        $parameters = $this->cleanParameterName($parameters);
        $sql = sprintf(
            'insert into %s (%s) values (%s)',
            $table,
            implode(', ', array_keys($parameters)),
            ':' . implode(', :', array_keys($parameters))
        );

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
        } catch (Exception $e) {
            $this->sendToLog($e);
        }
    }

    public function update($table,$id,$parameters)
    {
        $parameters = $this->cleanParameterName($parameters);
        if($parameters['diagnostico'] == NULL){
            $sql="UPDATE {$table} SET nombre = :nombre,
                                      email = :email,
                                      tel = :tel,
                                      edad = :edad,
                                      talla = :talla,
                                      altura = :altura,
                                      nacimiento = :nacimiento,
                                      cpelo = :cpelo,
                                      fechaturno = :fechaturno,
                                      horaturno = :horaturno 
                                  WHERE id = {$id}";
        }else{
            $sql="UPDATE {$table} SET nombre = :nombre,
                                      email = :email,
                                      tel = :tel,
                                      edad = :edad,
                                      talla = :talla,
                                      altura = :altura,
                                      nacimiento = :nacimiento,
                                      cpelo = :cpelo,
                                      fechaturno = :fechaturno,
                                      horaturno = :horaturno,
                                      diagnostico = :diagnostico,
                                      extension = :extension
                                  WHERE id = {$id}";
        }
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
        } catch (Exception $e) {
            $this->sendToLog($e);
        }
    }

    public function delete($table,$id)
    {
        try{
            $statement = $this->pdo->prepare("delete from {$table} where id = {$id}");
            $statement->execute();
        }catch (Exception $e) {
            $this->sendToLog($e);
        }
        
    }

    private function sendToLog(Exception $e)
    {
        if ($this->logger) {
            $this->logger->error('Error', ["Error" => $e]);
        }
    }

    /**
     * Limpia guiones - que puedan venir en los nombre de los parametros
     * ya que esto no funciona con PDO
     *
     * Ver: http://php.net/manual/en/pdo.prepared-statements.php#97162
     */
    private function cleanParameterName($parameters)
    {
        $cleaned_params = [];
        foreach ($parameters as $name => $value) {
            $cleaned_params[str_replace('-', '', $name)] = $value ;
        }
        return $cleaned_params;
    }
}
