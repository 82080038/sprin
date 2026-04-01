-- Add urutan column to bagian table
ALTER TABLE `bagian` ADD COLUMN `urutan` int(11) DEFAULT 0 AFTER `id_unsur`;

-- Update existing records with urutan based on their current order
SET @row_number = 0;
UPDATE bagian 
SET urutan = (@row_number:=@row_number + 1) 
ORDER BY id_unsur, id;

-- Create index for better performance
CREATE INDEX idx_bagian_unsur_urutan ON bagian (id_unsur, urutan);
