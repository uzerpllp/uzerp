--
-- $Revision: 1.1 $
--


--
-- DELETE MODULES AND PERMISSIONS
--


DELETE FROM permissions WHERE permission = 'ecommerce' AND type = 'm';
DELETE FROM permissions WHERE permission = 'gallery' AND type = 'm';
DELETE FROM permissions WHERE permission = 'intranet' AND type = 'm';
DELETE FROM permissions WHERE permission = 'websites' AND type = 'm';

DELETE FROM modules WHERE name = 'ecommerce';
DELETE FROM modules WHERE name = 'gallery';
DELETE FROM modules WHERE name = 'intranet';
DELETE FROM modules WHERE name = 'websites';


--
-- DROP MODULE TABLES
--


-- NEWS*

DROP VIEW newsletter_url_clicksoverview;
DROP VIEW newsletter_viewsoverview;
DROP VIEW newsletteroverview;

DROP TABLE news_items;
DROP TABLE newsletter_recipients;
DROP TABLE newsletter_url_clicks;
DROP TABLE newsletter_urls;
DROP TABLE newsletter_views;
DROP TABLE newsletters;


-- POLL*

DROP VIEW poll_options_overview;

DROP TABLE poll_votes;
DROP TABLE poll_options;
DROP TABLE polls;


-- WEB*

DROP VIEW webpagerolesoverview;
DROP VIEW webpagesoverview;
DROP VIEW website_filesoverview;
DROP VIEW websitesoverview;

DROP TABLE webpage_revisions;
DROP TABLE webpages;
DROP TABLE webpage_categories;
DROP TABLE webpageroles;
DROP TABLE website_admins;
DROP TABLE website_files;
DROP TABLE website_setup;


-- GALLERY

DROP TABLE gallery_pictures;
DROP TABLE galleries;


-- INTRANET

DROP VIEW intranet_page_accessoverview;
DROP VIEW intranet_page_filesoverview;
DROP VIEW intranet_page_typesoverview;
DROP VIEW intranet_section_filesoverview;

DROP TABLE intranet_config;
DROP TABLE intranet_page_revisions;
DROP TABLE intranet_page_access;
DROP TABLE intranet_page_files;
DROP TABLE intranet_pages;
DROP TABLE intranet_page_types;
DROP TABLE intranet_layouts;
DROP TABLE intranet_postings;
DROP TABLE intranet_section_access;
DROP TABLE intranet_section_files;
DROP TABLE intranet_sections;



-- ECOMMERCE

DROP VIEW customeroverview;
DROP VIEW customertypediscountoverview;
DROP VIEW orderoverview;
DROP VIEW orderitemoverview;
DROP VIEW productoverview;
DROP VIEW store_vouchersoverview;

DROP TABLE products_in_bundles;
DROP TABLE product_bundles;
DROP TABLE product_attributes;
DROP TABLE product_features;
DROP TABLE store_order_item_options;
DROP TABLE store_basket_item_options;
DROP TABLE product_options;
DROP TABLE product_option_categories;
DROP TABLE product_related_products;

DROP TABLE customer_type_discounts;
DROP TABLE store_offer_code_uses;
DROP TABLE store_offer_codes;
DROP TABLE customers_in_types;
DROP TABLE customer_types;

DROP TABLE dynamic_section_criteria;

DROP TABLE order_item_dispatches;
DROP TABLE order_shipments;
DROP TABLE order_item_additional_info;

DROP TABLE store_order_selected_extras;
DROP TABLE store_order_items;
DROP TABLE store_orders;

DROP TABLE store_basket_items;
DROP TABLE store_baskets;
DROP TABLE store_config;
DROP TABLE store_dynamic_sections;
DROP TABLE store_order_extras;
DROP TABLE store_product_information_requests;
DROP TABLE store_products;
DROP TABLE store_section_discounts;
DROP TABLE store_sections;
DROP TABLE store_suppliers;
DROP TABLE store_vouchers;

-- CUSTOMER*

DROP TABLE customers;
DROP TABLE websites;


DROP TABLE shipping_methods;
DROP TABLE shipping_options;
