<?php

namespace App\Repositories\EvaluationRepositories;

use PDO;

class EvaluationRepository
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Finds the submission by ID
     * @param string $submissionId
     * @return array|null
     */

    public function findSubmissionById(string $submissionId)
    {
        $sql = "
      SELECT * FROM evaluations
      WHERE submission_id = ?
      LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$submissionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }


    /**
     * Evaluates a submission by inserting evaluation data into the database
     * @param array $data
     */
    public function store(array $data): bool
    {
        $sql = "
        INSERT INTO evaluations (
          submission_id,
          client_type,
          evaluation_date,
          sex,
          age,
          region,
          service_availed,
          cc1,
          cc2,
          cc3,
          sqd0,
          sqd1,
          sqd2,
          sqd3,
          sqd4,
          sqd5,
          sqd6,
          sqd7,
          sqd8,
          suggestion,
          email
        ) VALUES (
          ?,
          ?, 
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?
        )
      ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
          $data['submission_id'],
          $data['client_type'],
          $data['transaction_date'],
          $data['sex'],
          $data['age'],
          $data['region'],
          $data['service_availed'],
          $data['cc1'],
          $data['cc2'],
          $data['cc3'],
          $data['sqd0'],
          $data['sqd1'],
          $data['sqd2'],
          $data['sqd3'],
          $data['sqd4'],
          $data['sqd5'],
          $data['sqd6'],
          $data['sqd7'],
          $data['sqd8'],
          $data['suggestion'],
          $data['email']
        ]);
    }

}
