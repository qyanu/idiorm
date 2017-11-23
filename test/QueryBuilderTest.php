<?php

class QueryBuilderTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Enable logging
        ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockPDO('sqlite::memory:');
        ORM::set_db($db);
    }

    public function tearDown() {
        ORM::reset_config();
        ORM::reset_db();
    }

    public function testFindMany() {
        ORM::for_table('widget')->find_many();
        $expected = "SELECT * FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testFindOne() {
        ORM::for_table('widget')->find_one();
        $expected = "SELECT * FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testFindOneWithPrimaryKeyFilter() {
        ORM::for_table('widget')->find_one(5);
        $expected = "SELECT * FROM `widget` WHERE `id` = ? LIMIT 1 {array (  0 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereIdIs() {
        ORM::for_table('widget')->where_id_is(5)->find_one();
        $expected = "SELECT * FROM `widget` WHERE `id` = ? LIMIT 1 {array (  0 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereIdIn() {
        ORM::for_table('widget')->where_id_in(array(4, 5))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `id` IN (?, ?) {array (  0 => 4,  1 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSingleWhereClause() {
        ORM::for_table('widget')->where('name', 'Fred')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` = ? LIMIT 1 {array (  0 => 'Fred',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleWhereClauses() {
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 10)->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` = ? AND `age` = ? LIMIT 1 {array (  0 => 'Fred',  1 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotEqual() {
        ORM::for_table('widget')->where_not_equal('name', 'Fred')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` != ? {array (  0 => 'Fred',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereLike() {
        ORM::for_table('widget')->where_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` LIKE ? LIMIT 1 {array (  0 => '%Fred%',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotLike() {
        ORM::for_table('widget')->where_not_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT LIKE ? LIMIT 1 {array (  0 => '%Fred%',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereIn() {
        ORM::for_table('widget')->where_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IN (?, ?) {array (  0 => 'Fred',  1 => 'Joe',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotIn() {
        ORM::for_table('widget')->where_not_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT IN (?, ?) {array (  0 => 'Fred',  1 => 'Joe',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereAnyIs() {
        ORM::for_table('widget')->where_any_is(array(
            array('name' => 'Joe', 'age' => 10),
            array('name' => 'Fred', 'age' => 20)))->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `name` = ? AND `age` = ? ) OR ( `name` = ? AND `age` = ? )) {array (  0 => 'Joe',  1 => 10,  2 => 'Fred',  3 => 20,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereAnyIsOverrideOneColumn() {
        ORM::for_table('widget')->where_any_is(array(
            array('name' => 'Joe', 'age' => 10),
            array('name' => 'Fred', 'age' => 20)), array('age' => '>'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `name` = ? AND `age` > ? ) OR ( `name` = ? AND `age` > ? )) {array (  0 => 'Joe',  1 => 10,  2 => 'Fred',  3 => 20,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereAnyIsOverrideAllOperators() {
        ORM::for_table('widget')->where_any_is(array(
            array('score' => '5', 'age' => 10),
            array('score' => '15', 'age' => 20)), '>')->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `score` > ? AND `age` > ? ) OR ( `score` > ? AND `age` > ? )) {array (  0 => '5',  1 => 10,  2 => '15',  3 => 20,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLimit() {
        ORM::for_table('widget')->limit(5)->find_many();
        $expected = "SELECT * FROM `widget` LIMIT 5";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLimitAndOffset() {
        ORM::for_table('widget')->limit(5)->offset(5)->find_many();
        $expected = "SELECT * FROM `widget` LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testOrderByDesc() {
        ORM::for_table('widget')->order_by_desc('name')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` DESC LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testOrderByAsc() {
        ORM::for_table('widget')->order_by_asc('name')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testOrderByExpression() {
        ORM::for_table('widget')->order_by_expr('SOUNDEX(`name`)')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY SOUNDEX(`name`) LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleOrderBy() {
        ORM::for_table('widget')->order_by_asc('name')->order_by_desc('age')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC, `age` DESC LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testGroupBy() {
        ORM::for_table('widget')->group_by('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleGroupBy() {
        ORM::for_table('widget')->group_by('name')->group_by('age')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name`, `age`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testGroupByExpression() {
        ORM::for_table('widget')->group_by_expr("FROM_UNIXTIME(`time`, '%Y-%m')")->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY FROM_UNIXTIME(`time`, '%Y-%m')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHaving() {
        ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = ? LIMIT 1 {array (  0 => 'Fred',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleHaving() {
        ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->having('age', 10)->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = ? AND `age` = ? LIMIT 1 {array (  0 => 'Fred',  1 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotEqual() {
        ORM::for_table('widget')->group_by('name')->having_not_equal('name', 'Fred')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` != ? {array (  0 => 'Fred',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingLike() {
        ORM::for_table('widget')->group_by('name')->having_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` LIKE ? LIMIT 1 {array (  0 => '%Fred%',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotLike() {
        ORM::for_table('widget')->group_by('name')->having_not_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT LIKE ? LIMIT 1 {array (  0 => '%Fred%',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingIn() {
        ORM::for_table('widget')->group_by('name')->having_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IN (?, ?) {array (  0 => 'Fred',  1 => 'Joe',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotIn() {
        ORM::for_table('widget')->group_by('name')->having_not_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT IN (?, ?) {array (  0 => 'Fred',  1 => 'Joe',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingLessThan() {
        ORM::for_table('widget')->group_by('name')->having_lt('age', 10)->having_gt('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` < ? AND `age` > ? {array (  0 => 10,  1 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingLessThanOrEqualAndGreaterThanOrEqual() {
        ORM::for_table('widget')->group_by('name')->having_lte('age', 10)->having_gte('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` <= ? AND `age` >= ? {array (  0 => 10,  1 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNull() {
        ORM::for_table('widget')->group_by('name')->having_null('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotNull() {
        ORM::for_table('widget')->group_by('name')->having_not_null('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NOT NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawHaving() {
        ORM::for_table('widget')->group_by('name')->having_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = ? AND (`age` = ? OR `age` = ?) {array (  0 => 'Fred',  1 => 5,  2 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testComplexQuery() {
        ORM::for_table('widget')->where('name', 'Fred')->limit(5)->offset(5)->order_by_asc('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = ? ORDER BY `name` ASC LIMIT 5 OFFSET 5 {array (  0 => 'Fred',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereLessThanAndGreaterThan() {
        ORM::for_table('widget')->where_lt('age', 10)->where_gt('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` < ? AND `age` > ? {array (  0 => 10,  1 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereLessThanAndEqualAndGreaterThanAndEqual() {
        ORM::for_table('widget')->where_lte('age', 10)->where_gte('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` <= ? AND `age` >= ? {array (  0 => 10,  1 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNull() {
        ORM::for_table('widget')->where_null('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotNull() {
        ORM::for_table('widget')->where_not_null('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NOT NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClause() {
        ORM::for_table('widget')->where_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = ? AND (`age` = ? OR `age` = ?) {array (  0 => 'Fred',  1 => 5,  2 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseWithPercentSign() {
        ORM::for_table('widget')->where_raw('STRFTIME("%Y", "now") = ?', array(2012))->find_many();
        $expected = "SELECT * FROM `widget` WHERE STRFTIME(\"%Y\", \"now\") = ? {array (  0 => 2012,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseWithNoParameters() {
        ORM::for_table('widget')->where_raw('`name` = "Fred"')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = \"Fred\"";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseInMethodChain() {
        ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where('size', 'large')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` = ? AND (`name` = ? OR `name` = ?) AND `size` = ? {array (  0 => 18,  1 => 'Fred',  2 => 'Bob',  3 => 'large',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseMultiples() {
        ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where_raw('(`name` = ? OR `name` = ?)', array('Sarah', 'Jane'))->where('size', 'large')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` = ? AND (`name` = ? OR `name` = ?) AND (`name` = ? OR `name` = ?) AND `size` = ? {array (  0 => 18,  1 => 'Fred',  2 => 'Bob',  3 => 'Sarah',  4 => 'Jane',  5 => 'large',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawQuery() {
        ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w')->find_many();
        $expected = "SELECT `w`.* FROM `widget` w";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawQueryWithParameters() {
        ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ?', array('Fred', 5))->find_many();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ? {array (  0 => 'Fred',  1 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawQueryWithNamedPlaceholders() {
        ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = :name AND `age` = :age', array(':name' => 'Fred', ':age' => 5))->find_many();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = :name AND `age` = :age {array (  ':name' => 'Fred',  ':age' => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSimpleResultColumn() {
        ORM::for_table('widget')->select('name')->find_many();
        $expected = "SELECT `name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleSimpleResultColumns() {
        ORM::for_table('widget')->select('name')->select('age')->find_many();
        $expected = "SELECT `name`, `age` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSpecifyTableNameAndColumnInResultColumns() {
        ORM::for_table('widget')->select('widget.name')->find_many();
        $expected = "SELECT `widget`.`name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMainTableAlias() {
        ORM::for_table('widget')->table_alias('w')->find_many();
        $expected = "SELECT * FROM `widget` `w`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testAliasesInResultColumns() {
        ORM::for_table('widget')->select('widget.name', 'widget_name')->find_many();
        $expected = "SELECT `widget`.`name` AS `widget_name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testAliasesInSelectManyResults() {
        ORM::for_table('widget')->select_many(array('widget_name' => 'widget.name'), 'widget_handle')->find_many();
        $expected = "SELECT `widget`.`name` AS `widget_name`, `widget_handle` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLiteralExpressionInResultColumn() {
        ORM::for_table('widget')->select_expr('COUNT(*)', 'count')->find_many();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLiteralExpressionInSelectManyResultColumns() {
        ORM::for_table('widget')->select_many_expr(array('count' => 'COUNT(*)'), 'SUM(widget_order)')->find_many();
        $expected = "SELECT COUNT(*) AS `count`, SUM(widget_order) FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSimpleJoin() {
        ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSimpleJoinWithWhereIdIsMethod() {
        ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_one(5);
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` WHERE `widget`.`id` = ? LIMIT 1 {array (  0 => 5,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInnerJoin() {
        ORM::for_table('widget')->inner_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLeftOuterJoin() {
        ORM::for_table('widget')->left_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` LEFT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRightOuterJoin() {
        ORM::for_table('widget')->right_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` RIGHT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testFullOuterJoin() {
        ORM::for_table('widget')->full_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` FULL OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleJoinSources() {
        ORM::for_table('widget')
        ->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))
        ->join('widget_nozzle', array('widget_nozzle.widget_id', '=', 'widget.id'))
        ->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` JOIN `widget_nozzle` ON `widget_nozzle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testJoinWithAliases() {
        ORM::for_table('widget')->join('widget_handle', array('wh.widget_id', '=', 'widget.id'), 'wh')->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testJoinWithAliasesAndWhere() {
        ORM::for_table('widget')->table_alias('w')->join('widget_handle', array('wh.widget_id', '=', 'w.id'), 'wh')->where_equal('id', 1)->find_many();
        $expected = "SELECT * FROM `widget` `w` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `w`.`id` WHERE `w`.`id` = ? {array (  0 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testJoinWithStringConstraint() {
        ORM::for_table('widget')->join('widget_handle', "widget_handle.widget_id = widget.id")->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON widget_handle.widget_id = widget.id";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawJoin() {
        ORM::for_table('widget')->raw_join('INNER JOIN ( SELECT * FROM `widget_handle` )', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle')->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` ) `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawJoinWithParameters() {
        ORM::for_table('widget')->raw_join('INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?)', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle', array('%button%', 2))->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?) `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` {array (  0 => '%button%',  1 => 2,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawJoinAndRawWhereWithParameters() {
        ORM::for_table('widget')
            ->raw_join('INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?)', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle', array('%button%', 2))
            ->raw_join('INNER JOIN ( SELECT * FROM `person` WHERE `person`.name LIKE ?)', array('person.id', '=', 'widget.person_id'), 'person', array('%Fred%'))
            ->where_raw('`id` > ? AND `id` < ?', array(5, 10))
            ->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?) `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` INNER JOIN ( SELECT * FROM `person` WHERE `person`.name LIKE ?) `person` ON `person`.`id` = `widget`.`person_id` WHERE `id` > ? AND `id` < ? {array (  0 => '%button%',  1 => 2,  2 => '%Fred%',  3 => 5,  4 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSelectWithDistinct() {
        ORM::for_table('widget')->distinct()->select('name')->find_many();
        $expected = "SELECT DISTINCT `name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertData() {
        $widget = ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES (?, ?) {array (  0 => 'Fred',  1 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertDataContainingAnExpression() {
        $widget = ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->set_expr('added', 'NOW()');
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`, `added`) VALUES (?, ?, NOW()) {array (  0 => 'Fred',  1 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertDataUsingArrayAccess() {
        $widget = ORM::for_table('widget')->create();
        $widget['name'] = "Fred";
        $widget['age'] = 10;
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES (?, ?) {array (  0 => 'Fred',  1 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testUpdateData() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = ?, `age` = ? WHERE `id` = ? {array (  0 => 'Fred',  1 => 10,  2 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testUpdateDataContainingAnExpression() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->set_expr('added', 'NOW()');
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = ?, `age` = ?, `added` = NOW() WHERE `id` = ? {array (  0 => 'Fred',  1 => 10,  2 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testUpdateMultipleFields() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = ?, `age` = ? WHERE `id` = ? {array (  0 => 'Fred',  1 => 10,  2 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testUpdateMultipleFieldsContainingAnExpression() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->set_expr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = ?, `age` = ?, `added` = NOW(), `lat_long` = GeomFromText('POINT(1.2347 2.3436)') WHERE `id` = ? {array (  0 => 'Fred',  1 => 10,  2 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testUpdateMultipleFieldsContainingAnExpressionAndOverridePreviouslySetExpression() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->set_expr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
        $widget->lat_long = 'unknown';
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = ?, `age` = ?, `added` = NOW(), `lat_long` = ? WHERE `id` = ? {array (  0 => 'Fred',  1 => 10,  2 => 'unknown',  3 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testDeleteData() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->delete();
        $expected = "DELETE FROM `widget` WHERE `id` = ? {array (  0 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testDeleteMany() {
        ORM::for_table('widget')->where_equal('age', 10)->delete_many();
        $expected = "DELETE FROM `widget` WHERE `age` = ? {array (  0 => 10,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testCount() {
        ORM::for_table('widget')->count();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }
    
    public function testIgnoreSelectAndCount() {
    	ORM::for_table('widget')->select('test')->count();
    	$expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
    	$this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMax() {
        ORM::for_table('person')->max('height');
        $expected = "SELECT MAX(`height`) AS `max` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMin() {
        ORM::for_table('person')->min('height');
        $expected = "SELECT MIN(`height`) AS `min` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testAvg() {
        ORM::for_table('person')->avg('height');
        $expected = "SELECT AVG(`height`) AS `avg` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSum() {
        ORM::for_table('person')->sum('height');
        $expected = "SELECT SUM(`height`) AS `sum` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function test_quote_identifier_part() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->set('added', '2013-01-04');
        $widget->save();
        $expected = "UPDATE `widget` SET `added` = ? WHERE `id` = ? {array (  0 => '2013-01-04',  1 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }
    
    public function test_quote_multiple_identifiers_part() {
        $record = ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $expected = "`id1`, `id2`";
        $this->assertEquals($expected, $record->_quote_identifier($record->_get_id_column_name()));
    }
    
    /**
     * Compound primary key tests
     */
    public function testFindOneWithCompoundPrimaryKey() {
        $record = ORM::for_table('widget')->use_id_column(array('id1', 'id2'));
        $record->findOne(array('id1' => 10, 'name' => 'Joe', 'id2' => 20));
        $expected = "SELECT * FROM `widget` WHERE `id1` = ? AND `id2` = ? LIMIT 1 {array (  0 => 10,  1 => 20,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertWithCompoundPrimaryKey() {
        $record = ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $record->set('id1', 10);
        $record->set('id2', 20);
        $record->set('name', 'Joe');
        $record->save();
        $expected = "INSERT INTO `widget` (`id1`, `id2`, `name`) VALUES (?, ?, ?) {array (  0 => 10,  1 => 20,  2 => 'Joe',)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testUpdateWithCompoundPrimaryKey() {
        $record = ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $record->set('id1', 10);
        $record->set('id2', 20);
        $record->set('name', 'Joe');
        $record->save();
        $record->set('name', 'John');
        $record->save();
        $expected = "UPDATE `widget` SET `name` = ? WHERE `id1` = ? AND `id2` = ? {array (  0 => 'John',  1 => 10,  2 => 20,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testDeleteWithCompoundPrimaryKey() {
        $record = ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $record->set('id1', 10);
        $record->set('id2', 20);
        $record->set('name', 'Joe');
        $record->save();
        $record->delete();
        $expected = "DELETE FROM `widget` WHERE `id1` = ? AND `id2` = ? {array (  0 => 10,  1 => 20,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereIdInWithCompoundPrimaryKey() {
        $record = ORM::for_table('widget')->use_id_column(array('id1', 'id2'));
        $record->where_id_in(array(
            array('id1' => 10, 'name' => 'Joe', 'id2' => 20),
            array('id1' => 20, 'name' => 'Joe', 'id2' => 30)))->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `id1` = ? AND `id2` = ? ) OR ( `id1` = ? AND `id2` = ? )) {array (  0 => 10,  1 => 20,  2 => 20,  3 => 30,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    /**
     * Regression tests
     */
    public function testIssue12IncorrectQuotingOfColumnWildcard() {
        ORM::for_table('widget')->select('widget.*')->find_one();
        $expected = "SELECT `widget`.* FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testIssue57LogQueryRaisesWarningWhenPercentSymbolSupplied() {
        ORM::for_table('widget')->where_raw('username LIKE "ben%"')->find_many();
        $expected = 'SELECT * FROM `widget` WHERE username LIKE "ben%"';
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testIssue57LogQueryRaisesWarningWhenQuestionMarkSupplied() {
        ORM::for_table('widget')->where_raw('comments LIKE "has been released?%"')->find_many();
        $expected = 'SELECT * FROM `widget` WHERE comments LIKE "has been released?%"';
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testIssue74EscapingQuoteMarksIn_quote_identifier_part() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->set('ad`ded', '2013-01-04');
        $widget->save();
        $expected = "UPDATE `widget` SET `ad``ded` = ? WHERE `id` = ? {array (  0 => '2013-01-04',  1 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testIssue90UsingSetExprAloneDoesTriggerQueryGeneration() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->set_expr('added', 'NOW()');
        $widget->save();
        $expected = "UPDATE `widget` SET `added` = NOW() WHERE `id` = ? {array (  0 => 1,)}";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testIssue176LimitDoesntWorkFirstTime() {
        ORM::reset_config();
        ORM::reset_db();

        ORM::configure('logging', true);
        ORM::configure('connection_string', 'sqlite::memory:');

        ORM::for_table('sqlite_master')->limit(1)->find_array();
        $expected = "SELECT * FROM `sqlite_master` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }
}

