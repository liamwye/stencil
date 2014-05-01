<table>
    <?php if (isset($caption)) : ?>
        <caption><?php echo $caption; ?></caption>
    <?php endif; ?>

    <?php foreach ($data as $header => $row) : ?>
        <tr>
            <?php if (is_string($header)) : ?>
                <th><?php echo $header; ?></th>
            <?php endif; ?>
            <?php if (is_array($row)) : ?>
                <?php foreach ($row as $r) : ?>
                    <td><?php echo (is_scalar($r) ? $r : print_r($r, true)); ?></td>
                <?php endforeach; ?>
            <?php else: ?>
                <td><?php echo $this->convertToString($row); ?></td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>