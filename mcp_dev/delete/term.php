term is unique in that there are two types of deletes. One type
of delete will remove only the term and move all children up one. The other
will remove the term and all of its children. Also, unlike nodes terms do not
have comments that need to be deleted.

-- delete field values
-- delete references to terms to be deleted
-- delete user and role permissions
-- delete term

--------------------------------------------------------------

In case of removal the terms that are a direct child will be moved up
one slot.

- here we only delete one term but update several

- So we need to delete one term, get all its immediate children and move
them to the parent of the target term.

|| removeTerm() -> calls delete term for single item

We run into an edge case here where a terms system name and human name are unique
per branch. So we need to make sure that is met before moving the child terms up
one branch.

-------------------------------------------------------------

In the case of full deletion the term and all its children will need to be deleted. Need
to determine the most optimized manor of doing this that scales well.

- here we likely delete multiple terms

Probably the best method here is to create an array of all children and add the target term. Than
delete all the other information.

deleteTerm() -> add exra argument here whether to rmeove children or not

----------------------------------------------------------------

I would like to implement this is a method inside the dao class that can be resued
in different contexts like menu links as well. Considering both use an adjacency list
a common delete and remove function should be feasible.







