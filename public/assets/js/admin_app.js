$(function () {
    // Scripts for admin app
        const rowsPerPage = 2;

        $('.custom-table').each(function() {
            const $table = $(this);
            const $rows = $table.find('tbody tr');
            const $pagination = $table.closest('.table-container').find('.custom-pagination');
            const $pageNumbers = $pagination.find('.page-numbers');
            const $prevButton = $pagination.find('.btn-prev');
            const $nextButton = $pagination.find('.btn-next');
            let currentPage = 1;

            function displayRows() {
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                $rows.hide().slice(start, end).show();
                updatePageNumbers();
            }

            function updatePageNumbers() {
                const totalPages = Math.ceil($rows.length / rowsPerPage);
                $pageNumbers.text(`Page ${currentPage} of ${totalPages}`);
            }

            $nextButton.on('click', function() {
                if (currentPage < Math.ceil($rows.length / rowsPerPage)) {
                    currentPage++;
                    displayRows();
                }
            });

            $prevButton.on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    displayRows();
                }
            });

            displayRows();
        });
})