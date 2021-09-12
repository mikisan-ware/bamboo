<?php

$qry    = Query::build()->from("test")->where(["param1", 1], ["param2", 2])->select("*");
$this->bamboo->query($qry);

$this->bamboo->query(
        Query::build()
            ->from("test@A")
            ->leftJoin("other@B")
            ->on("A.id", "B.id")
            ->where([["param1", 1], ["param2", 2]])
            ->select("A.*")
        );

$this->bamboo->query(
        Query::build()
            ->from(
                    Query::build()
                        ->select("@seq_no := 0 AS seq_no")
                        ->union()
                        ->select("@seq_no := @seq_no + 1 AS seq_no")->from("information_schema.COLUMNS")
                        ->limit($diff)
                        ->as("tmp")
            )
        );

echo <<< EOL
SELECT [:start_date] + INTERVAL seq_no DAY AS date
FROM (
    SELECT @seq_no := 0 AS seq_no
    UNION
    SELECT @seq_no := @seq_no + 1 AS seq_no FROM information_schema.COLUMNS
    LIMIT {$diff}
) AS tmp
EOL;

$this->bamboo->query(
        Query::build()
            ->from(
                    Query::build()
                        ->select("@seq_no := 0 AS seq_no")
                        ->union()
                        ->select("@seq_no := @seq_no + 1 AS seq_no")->from("information_schema.COLUMNS")
                        ->limit($diff)
                        ->as("tmp")
            )->as("A")
            ->leftJoin(
                    Query::build()
                        ->from(feijoa_jp2.covid19_cities)
                        ->where(["type", $type])
                        ->groupBy("date")
                        ->select("
                            DATE_FORMAT(date_confirm, [:date_format]) AS date,
                            COUNT(date_confirm) as count
                            ")
            )->as("B")
            ->using("date")
            ->orderBy("A.date", OrderBy::ASC)
            ->select("
                    A.date,
                    [$type] as type,
                    CASE
                        WHEN B.count IS NULL THEN 0
                        ELSE B.count
                    END AS count
                    ")
        );
    
echo <<< EOL
(
    SELECT
        A.date,
        [$type] as type,
        CASE
                WHEN B.count IS NULL THEN 0
                ELSE B.count
        END AS count
    FROM (
        SELECT [:start_date] + INTERVAL seq_no DAY AS date
        FROM (
            SELECT @seq_no := 0 AS seq_no
            UNION
            SELECT @seq_no := @seq_no + 1 AS seq_no FROM information_schema.COLUMNS
            LIMIT [$diff]
        ) AS tmp
    ) AS A
    LEFT JOIN
    (
        SELECT
                DATE_FORMAT(`date_confirm`, [:date_format]) AS date,
                COUNT(`date_confirm`) as count
        FROM `feijoa_jp2`.`covid19_cities`
        WHERE type = [$type]
        GROUP BY date
    ) AS B
    USING(date)
    ORDER BY A.date ASC
)
EOL;