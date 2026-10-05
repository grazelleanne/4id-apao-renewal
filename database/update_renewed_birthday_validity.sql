-- Run in Adminer on the production database after exporting a backup.
-- Uses the recorded renewal/approval year, rather than renewing again today.
-- Personnel without a recorded renewal date or birthday are skipped.
START TRANSACTION;

CREATE TEMPORARY TABLE birthday_validity_updates AS
SELECT source.id, source.item_number, source.last_name, source.first_name,
       source.date_of_validity AS previous_validity,
       DATE_ADD(
           STR_TO_DATE(CONCAT(YEAR(source.renewal_date) + 2, '-',
                              DATE_FORMAT(source.date_of_birth, '%m'), '-01'), '%Y-%m-%d'),
           INTERVAL (LEAST(DAY(source.date_of_birth), DAY(LAST_DAY(
               STR_TO_DATE(CONCAT(YEAR(source.renewal_date) + 2, '-',
                                  DATE_FORMAT(source.date_of_birth, '%m'), '-01'), '%Y-%m-%d')
           ))) - 1) DAY
       ) AS new_validity
FROM (
    SELECT p.*, COALESCE(p.last_renewed_at, p.date_approved,
        (SELECT MAX(h.created_at) FROM renewal_history h
         WHERE h.item_number=p.item_number AND h.action='renewed'),
        (SELECT MAX(i.inspected_at) FROM inspections i
         WHERE i.personnel_id=p.id AND i.status='approved')) AS renewal_date
    FROM personnel p
    WHERE p.archived_at IS NULL
      AND (p.date_of_validity > DATE_ADD(CURDATE(), INTERVAL 60 DAY)
           OR (p.date_of_validity IS NULL AND (p.approved_status='renewed' OR p.ics_status='ready')))
) AS source
WHERE source.date_of_birth IS NOT NULL AND source.renewal_date IS NOT NULL
  AND source.date_of_birth > '0000-00-00';

-- Shows exactly which existing renewed records are being corrected.
SELECT * FROM birthday_validity_updates ORDER BY item_number;

UPDATE personnel p JOIN birthday_validity_updates u ON u.id=p.id
SET p.date_of_validity=u.new_validity, p.updated_at=NOW()
WHERE NOT (p.date_of_validity <=> u.new_validity);

UPDATE inspections i JOIN birthday_validity_updates u ON u.id=i.personnel_id
SET i.next_renewal_date=u.new_validity, i.updated_at=NOW()
WHERE i.status='approved'
  AND i.id=(SELECT latest.id FROM
      (SELECT personnel_id, MAX(id) AS id FROM inspections GROUP BY personnel_id) latest
      WHERE latest.personnel_id=i.personnel_id);

COMMIT;
DROP TEMPORARY TABLE birthday_validity_updates; 
