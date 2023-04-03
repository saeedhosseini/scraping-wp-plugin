<?php
global $wpdb;

$indexPage = $_GET['paged'] ?? 1;
$sort = $_GET['sort_by'] ?? 'publish';
$perPage = $_GET['per_page'] ?? 25;
$cat_parent_id = $_GET['cat_p_id'] ?? -1;
$cat_lvl2_id = $_GET['cat_lvl2_id'] ?? -1;
$cat_id = $_GET['cat_id'] ?? -1;

$categories = TermTaxonomy::query()->where('parent', 0)->where('taxonomy', 'product_cat')
    ->with(['term'])->get();

//$currency = Currency::all();

$childCatLvl2 = null;
if ($cat_parent_id > 0)
    $childCatLvl2 = TermTaxonomy::query()->where('parent', $cat_parent_id)->where('taxonomy', 'product_cat')
        ->with(['term'])->get();

$childCatData = null;
if ($cat_lvl2_id > 0)
    $childCatData = TermTaxonomy::query()->where('parent', $cat_lvl2_id)->where('taxonomy', 'product_cat')
        ->with(['term'])->get();

function index(int $page, int $perPage, $sort, int $catID, int $catLvl2)
{
    $pQuery = Product::query()->with(['category', 'dataAttributes', 'dataAttributes.attributeName']);

    if ($sort == 'publish')
        $pQuery = $pQuery->orderByDesc('product_woocommerce_id');
    else if ($sort == 'new')
        $pQuery = $pQuery->orderByDesc('updated_at');
    else if ($sort == 'old')
        $pQuery = $pQuery->orderBy('updated_at');
    else
        $pQuery = $pQuery->orderBy('product_woocommerce_id');

    if ($catID > 0)
        $pQuery = $pQuery->whereHas('category', fn($Q) => $Q->where('woo_category_id', $catID));

    if ($catID <= 0 && $catLvl2 > 0)
        $pQuery = $pQuery->whereHas('category', fn($Q) => $Q->where('woo_category_id', $catLvl2));

    return $pQuery->skip(($page - 1) * $perPage)->take($perPage)->get();
}

?>

<html data-theme="light">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=devide-width, initial-scale=1.0"/>
    <link
            href="https://cdn.jsdelivr.net/npm/daisyui@2.6.0/dist/full.css"
            rel="stylesheet"
            type="text/css"
    />
    <link
            href="https://cdn.jsdelivr.net/npm/basscss@latest/css/basscss.min.css"
            rel="stylesheet"
            type="text/css"
    />
</head>
<body>
<div>
    <select id="catMain" class="select w-full max-w-xs"
            onchange="onChangeParent()">
        <option value=-1>همه</option>
        <?php $categories->each(function ($item) use ($cat_parent_id) {
            $s = '';
            if ($item->term_id == $cat_parent_id) {
                $s = 'selected';
            }
            echo "<option value={$item->term_id} {$s}>{$item->term->name}</option>";
        }) ?>
    </select>

    <select id="catLv2" <?php echo $cat_parent_id > 0 ?: "disabled" ?> class="select w-full max-w-xs"
            onchange="onChangeLvl2()">
        <option value=-1>دسته بندی انتخاب کنید</option>
        <?php
        if ($childCatLvl2 != null) {
            $childCatLvl2->each(function ($item) use ($cat_lvl2_id) {
                $s = '';
                if ($item->term_id == $cat_lvl2_id) {
                    $s = 'selected';
                }
                echo "<option value={$item->term_id} {$s}>{$item->term->name}</option>";
            });
        }
        ?>
    </select>

    <select id="catChild" <?php echo $cat_parent_id > 0 ?: "disabled" ?> class="select w-full max-w-xs"
            onchange="onChangeChild()">
        <option value=-1>دسته بندی انتخاب کنید</option>
        <?php
        if ($childCatData != null) {
            $childCatData->each(function ($item) use ($cat_id) {
                $s = '';
                if ($item->term_id == $cat_id) {
                    $s = 'selected';
                }
                echo "<option value={$item->term_id} {$s}>{$item->term->name}</option>";
            });
        }
        ?>
    </select>

    <select id="mySelect" class="select w-full max-w-xs" onchange="onChangeSort()">
        <option disabled selected>مرتب سازی بر اساس:</option>
        <option value="publish">منتشر شده (پیشفرض)</option>
        <option value="new">جدید</option>
        <option value="old">قدیمی</option>
        <option value="draft">منتشر نشده</option>
    </select>

    <span>مرتب: <?php echo $sort == 'publish' ? ('منتشر شده') : ($sort == 'new' ? 'جدیدترین' : (
        $sort == 'old' ? 'قدیمی ترین' : 'منتشر نشده'
        )) ?></span>

    <select id="perPageSelect" class="select w-full max-w-xs" onchange="onChangePerPage()">
        <option disabled selected>تعداد نمایش:</option>
        <option value=25>25</option>
        <option value=50>50</option>
        <option value=100>100</option>
        <option value=200>200</option>
    </select>

    <span>تعداد : <?php echo $perPage ?></span>

    <button class="btn" <?php echo $indexPage != 1 ?: "disabled" ?> >
        <a href=
           <?php
           if ($indexPage == 1) {
               echo "";
           } else
               echo "admin.php?page=scrap-plugin&paged=" . ($indexPage - 1)
                   . '&sort_by=' . $sort
                   . '&per_page=' . $perPage ?>
        >صفحه قبل</a>
    </button>

    <span>صفحه : <?php echo $indexPage ?></span>

    <button class="btn">
        <a href=
           <?php echo "admin.php?page=scrap-plugin&paged=" . ($indexPage + 1)
               . '&sort_by=' . $sort
               . '&per_page=' . $perPage ?>
        >صفحه بعد</a>
    </button>


</div>
<div class="overflow-x-auto" dir="ltr">
    <table class="table table-compact w-full">
        <thead>
        <tr>
            <th>#</th>
            <th>category</th>
            <th>name</th>
            <th>price</th>
            <th>price Unit</th>
            <th>price T</th>
            <th>image</th>
            <th>link</th>
            <th>actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach (index($indexPage, $perPage, $sort, $cat_id, $cat_lvl2_id) as $result) {
            $items = [];
            foreach ($result->dataAttributes as $attribute) {
                if ($attribute->attributeName != null && $attribute->attributeName->attr_name != null)
                    $items[$attribute->attributeName->attr_name] = $attribute->attr_value;
            }
            echo "<tr>";
            echo "<th>{$result['id']}</th>";
            echo "<td>{$result['category_id']}</td>";
            if (array_key_exists('name', $items))
                echo "<td>{$items['name']}</td>";
            else
                echo "<td></td>";
            if (array_key_exists('price', $items))
                echo "<td>{$items['price']}</td>";
            else
                echo "<td></td>";
            echo "<td>Lir</td>";
            //$toman = $items['price'] * ($currency->where('origin' , 'lir')
            //     ->where('exchange' , 'rial')->first()?->rate ?? 0);
            //echo "<td>$toman</td>";
            echo "<td>0</td>";
            if (array_key_exists('_thumbnail', $items))
                echo "<td><img src={$items['_thumbnail']}/></td>";
            else
                echo "<td></td>";
            echo "<td><a href={$result['link']} class='link'>link</td>";
            if ($result['product_woocommerce_id'] != null) {
                echo "<td><a href={$result['link']} class='link'>update</td>";
            } else {
                echo "<td><a href={$result['link']} class='link'>add</td>";
            }
            echo "</tr>";
        }
        ?>

        </tbody>
    </table>
</div>

<script>
    function onChangeSort() {
        const x = document.getElementById("mySelect").value;
        window.location = 'admin.php?page=scrap-plugin&paged=1&sort_by=' + x + '&per_page=25'
            + "&cat_p_id=" + <?php echo "\"" . $cat_parent_id . "\"" ?>
            +"&cat_id=" + <?php echo "\"" . $cat_id . "\"" ?>;
    }

    function onChangeParent() {
        const x = document.getElementById("catMain").value;
        window.location = 'admin.php?page=scrap-plugin&paged=1&sort_by=' + <?php echo "\"" . $sort . "\"" ?>
            +'&per_page=' + <?php echo "\"" . $perPage . "\"" ?> +"&cat_p_id=" + x
            + "&cat_id=-1&cat_lvl2_id=-1";
    }

    function onChangeLvl2() {
        const x = document.getElementById("catLv2").value;
        window.location = 'admin.php?page=scrap-plugin&paged=1&sort_by=' + <?php echo "\"" . $sort . "\"" ?>
            +'&per_page=' + <?php echo "\"" . $perPage . "\"" ?> +"&cat_p_id=" + <?php echo "\"" . $cat_parent_id . "\"" ?>
            +"&cat_id=-1&cat_lvl2_id=" + x;
    }

    function onChangeChild() {
        const x = document.getElementById("catChild").value;
        window.location = 'admin.php?page=scrap-plugin&paged=1&sort_by=' + <?php echo "\"" . $sort . "\"" ?>
            +'&per_page=' + <?php echo "\"" . $perPage . "\"" ?> +"&cat_p_id="
            + <?php echo "\"" . $cat_parent_id . "\"" ?>
            +"&cat_id=" + x + "&cat_lvl2_id=" + <?php echo "\"" . $cat_lvl2_id . "\"" ?>;
    }

    function onChangePerPage() {
        const x = document.getElementById("perPageSelect").value;
        window.location = 'admin.php?page=scrap-plugin&paged=1&sort_by='
            + <?php echo "\"" . $sort . "\"" ?> +'&per_page='
            + x + "&cat_p_id=" + <?php echo "\"" . $cat_parent_id . "\"" ?>
            +"&cat_id=" + <?php echo "\"" . $cat_id . "\"" ?>;
    }
</script>
</body>
</html>
