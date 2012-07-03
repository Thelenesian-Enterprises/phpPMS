-- 0.91b

ALTER TABLE `config` DROP INDEX `vacParameter`, ADD UNIQUE `vacParameter` (`vacParameter`);