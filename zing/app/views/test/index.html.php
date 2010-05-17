<h1>This is the test view <?= h('foo') ?></h1>

<h2>Bleem</h2>

<?= c("#bleem.foo.bar", ^{
    return c('ul', ^{
        return join('', map(array('item 1', 'item 2', 'item 3'), ^{
            return c('li', $_);
        }));
    });
}); ?>

<?= trim(' fffff   ') ?>

<?= i('zing.png') ?>

<?= text_input('foo', 'hello') ?>

<?= textarea('raaa', 'Here is some content to edit') ?>
