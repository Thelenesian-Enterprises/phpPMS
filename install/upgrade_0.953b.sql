-- 0.91b
ALTER TABLE `config` DROP INDEX `vacParameter`, ADD UNIQUE `vacParameter` (`vacParameter`);
-- 0.953b
INSERT INTO config VALUES ('allowed_exts','bak,csv,doc,docx,gif,jpg,ods,odt,pdf,png,txt,vsd,xls,xsl');
INSERT INTO config VALUES ('filesenabled',1);
INSERT INTO config VALUES ('allowed_size',1024);