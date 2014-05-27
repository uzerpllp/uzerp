--
-- $Revision: 1.2 $
--

ALTER TABLE injector_classes DROP CONSTRAINT injector_classes_usercompanyid_fkey;

INSERT INTO injector_classes
 (name, category, class_name, description, usercompanyid)
  VALUES ('TaxCalculation', 'SY', 'UKTaxCalculator', NULL, 1);
INSERT INTO injector_classes
 (name, category, class_name, description, usercompanyid)
  VALUES ('Redirection', 'SY', 'RedirectHandler', NULL, -1);
INSERT INTO injector_classes
 (name, category, class_name, description, usercompanyid)
  VALUES ('Translation', 'SY', 'FileReadingTranslator', NULL, -1);
INSERT INTO injector_classes
 (name, category, class_name, description, usercompanyid)
  VALUES ('AuthenticationGateway', 'SY', 'DatabaseAuthenticator', NULL, -1);
INSERT INTO injector_classes
 (name, category, class_name, description, usercompanyid)
  VALUES ('LoginHandler', 'SY', 'HTMLFormLoginHandler', NULL, -1);

