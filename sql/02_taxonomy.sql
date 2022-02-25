
START TRANSACTION;
SELECT 'CREATING TAXONOMY' AS '';
DROP TABLE IF EXISTS taxonomy;
CREATE TABLE taxonomy(Taxonomy_ID INTEGER, Domain VARCHAR(25), Kingdom VARCHAR(25), Phylum VARCHAR(30), Class VARCHAR(25), TaxOrder VARCHAR(30), Family VARCHAR(25), Genus VARCHAR(40), Species VARCHAR(50));
CREATE INDEX TaxID_Index ON taxonomy (Taxonomy_ID);
CREATE INDEX Domain_Index ON taxonomy (Domain);
CREATE INDEX Kingdom_Index ON taxonomy (Kingdom); 
CREATE INDEX Phylum_Index ON taxonomy (Phylum); 
CREATE INDEX Class_Index ON taxonomy (Class); 
CREATE INDEX TaxOrder_Index ON taxonomy (TaxOrder); 
CREATE INDEX Family_Index ON taxonomy (Family); 
CREATE INDEX Genus_Index ON taxonomy (Genus); 
CREATE INDEX Species_Index ON taxonomy (Species); 
SELECT 'LOADING taxonomy' AS '';
LOAD DATA LOCAL INFILE '/private_stores/gerlt/databases/20211118/output/taxonomy.tab' INTO TABLE taxonomy;
COMMIT;

