<h1>This is the test view <?= h('foo') ?></h1>

<?= flash_messages() ?>

<?= error_messages($errors) ?>

<h2>Bleem</h2>

<?= c("#bleem.foo.bar", ^{
    return c('ul', ^{
        return join('', map(array('item 1', 'item 2', 'item 3'), ^{
            return c('li', $_);
        }));
    });
}); ?>

<?= start_form('', array('id' => 'moose')) ?>

  <?= trim(' fffff   ') ?>

  <?= i('zing.png') ?>

  <?= text_field('foo', 'hello') ?>

  <?= hidden_fields(array('foo' => 'bar', 'baz' => array('a' => 1, 'b' => 2, 'c' => 3))); ?>

  <?= check_box('moose', true) ?>
  <?= file_field('barz') ?>

  <?= textarea('raaa', 'Here is some content to edit') ?>

<?= end_form() ?>

<?= pager(array('page_count' => 5, 'page' => 3)) ?>

<?= start_context_menu() ?>
  <?= context_menu_item('pencil', 'Foo', '#') ?>
<?= end_context_menu() ?>