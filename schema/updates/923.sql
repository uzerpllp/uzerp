CREATE SEQUENCE pl_journals_id_seq
  INCREMENT 1
  MINVALUE 1
  START 1;

CREATE SEQUENCE sl_journals_id_seq
  INCREMENT 1
  MINVALUE 1
  START 1;

update pltransactions
   set description = our_reference||' - '||description
 where transaction_type='J'
   and description != our_reference
   and description!=''
   and description is not null;

update sltransactions
   set description = our_reference||' - '||description
 where transaction_type='J'
   and description != our_reference
   and description!=''
   and description is not null;

update pltransactions
   set description = our_reference
 where transaction_type='J'
   and (description=''
   or description is null);

update sltransactions
   set description = our_reference
 where transaction_type='J'
   and (description=''
   or description is null);

update pltransactions
   set our_reference = nextval('pl_journals_id_seq')
 where transaction_type='J';

update sltransactions
   set our_reference = nextval('sl_journals_id_seq')
 where transaction_type='J';

update gl_transactions
   set comment = docref
 where type='J'
   and source='P'
   and (comment=''
     or comment is null);

update gl_transactions
   set comment = docref
 where type='J'
   and source='S'
   and (comment=''
     or comment is null);

update gl_transactions
   set comment = docref||' - '||comment
 where type='J'
   and source='P'
   and docref != comment;

update gl_transactions
   set comment = docref||' - '||comment
 where type='J'
   and source='S'
   and docref != comment;

--
-- Now match the GL Trnasactions journals to the Purchase Ledger Transactions Journals
--

-- run this first to manually resolve rows that cannot be uniquely identified
-- usually due to two journals with same amount - i.e. moving between accounts
select id, count(*)
  from (select gl.id
          from gl_transactions gl
          join pltransactions pl on gl.transaction_date = pl.transaction_date
                                and gl.docref = pl.description
                                and (gl.value = pl.base_net_value
                                  or -gl.value = pl.base_net_value)
                                and pl.transaction_type='J'
         where type   = 'J'
           and source = 'P') as pl
 group by id
 having count(*)>1;

-- gives detail 
select gl.id, gl.docref, gl.comment, gl.value
     , pl.id, pl.our_reference, pl.description, pl.base_net_value
  from gl_transactions gl
  join pltransactions pl on gl.transaction_date = pl.transaction_date
                        and gl.docref = pl.description
                        and (gl.value = pl.base_net_value
                          or -gl.value = pl.base_net_value)
                        and pl.transaction_type='J'
 where type   = 'J'
   and source = 'SP'
 order by gl.id;

update gl_transactions
   set comment = docref
 where type = 'J'
   and source = 'P'
   and (comment = ''
     or comment is null);

update gl_transactions
   set comment = docref||' - '||comment
 where type = 'J'
   and source = 'P'
   and comment != docref;

update gl_transactions
   set docref = (select our_reference
                   from pltransactions pl
                  where gl_transactions.transaction_date = pl.transaction_date
                        and gl_transactions.docref = pl.description
                        and (gl_transactions.value = pl.base_net_value
                          or -gl_transactions.value = pl.base_net_value)
                        and pl.transaction_type='J')
 where type   = 'J'
   and source = 'P'
   and exists (select our_reference
                   from pltransactions pl
                  where gl_transactions.transaction_date = pl.transaction_date
                        and gl_transactions.docref = pl.description
                        and (gl_transactions.value = pl.base_net_value
                          or -gl_transactions.value = pl.base_net_value)
                        and pl.transaction_type='J');

-- run this to check for mismatches
-- initially this will pick up the rows where our_reference and description were concatenated
-- in pl transactions
select id, docref, comment, value
  from gl_transactions gl
 where source = 'P'
   and type = 'J'
   and not exists (select 1
                     from pltransactions pl
                    where pl.transaction_type='J'
                      and pl.our_reference = gl.docref);


--
-- Now match the GL Trnasactions journals to the Sales Ledger Transactions Journals
--
select id, count(*)
  from (select gl.id
          from gl_transactions gl
          join sltransactions sl on gl.transaction_date = sl.transaction_date
                                and gl.docref = sl.description
                                and (gl.value = sl.base_net_value
                                  or -gl.value = sl.base_net_value)
                                and sl.transaction_type='J'
         where type   = 'J'
           and source = 'S') as sl
 group by id
 having count(*)>1
 order by id;

-- gives detail 
select gl.id, gl.docref, gl.comment, gl.value
     , sl.id, sl.our_reference, sl.description, sl.base_net_value
  from gl_transactions gl
  join sltransactions sl on gl.transaction_date = sl.transaction_date
                        and gl.docref = sl.description
                        and (gl.value = sl.base_net_value
                          or -gl.value = sl.base_net_value)
                        and sl.transaction_type='J'
 where type   = 'J'
   and source = 'S'
 order by gl.id;

update gl_transactions
   set comment = docref
 where type = 'J'
   and source = 'S'
   and (comment = ''
     or comment is null);

update gl_transactions
   set comment = docref||' - '||comment
 where type = 'J'
   and source = 'S'
   and comment != docref;

update gl_transactions
   set docref = (select our_reference
                   from sltransactions sl
                  where gl_transactions.transaction_date = sl.transaction_date
                        and gl_transactions.docref = sl.description
                        and (gl_transactions.value = sl.base_net_value
                          or -gl_transactions.value = sl.base_net_value)
                        and sl.transaction_type='J')
 where type   = 'J'
   and source = 'S'
   and exists (select our_reference
                   from sltransactions sl
                  where gl_transactions.transaction_date = sl.transaction_date
                        and gl_transactions.docref = sl.description
                        and (gl_transactions.value = sl.base_net_value
                          or -gl_transactions.value = sl.base_net_value)
                        and sl.transaction_type='J');

-- run this to check for mismatches
-- initially this will pick up the rows where our_reference and description were concatenated
-- in pl transactions
select id, docref, comment, value
  from gl_transactions gl
 where source = 'S'
   and type = 'J'
   and not exists (select 1
                     from sltransactions sl
                    where sl.transaction_type='J'
                      and sl.our_reference = gl.docref);

