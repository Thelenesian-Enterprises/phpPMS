-- Sólo para actualizar desde v0.9b a v0.91b
--
-- Only for update from v0.9b to v0.91b
--
ALTER TABLE `phppms`.`config` DROP INDEX `vacParameter`, ADD UNIQUE `vacParameter` (`vacParameter`);