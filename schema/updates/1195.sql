insert into permissions
(permission, type, title, display, parent_id, position)
select 'cancel_lines', 'a', 'Cancel Lines', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='sorders')) as next
 where type='c'
   and permission='sorders';