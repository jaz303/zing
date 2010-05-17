<h1>This is the test view <?= h('foo') ?></h1>

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

  <?= text_field_tag('foo', 'hello') ?>

  <?= hidden_field_tags(array('foo' => 'bar', 'baz' => array('a' => 1, 'b' => 2, 'c' => 3))); ?>

  <?= check_box_tag('moose', true) ?>
  <?= file_field_tag('barz') ?>

  <?= textarea_tag('raaa', 'Here is some content to edit') ?>

<?= end_form() ?>