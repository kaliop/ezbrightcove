/* Current page input is assumed to be 0 indexed */
function klpbcPaginator(pageSize, currentPage, itemsTotal) {

    this.pageSize = pageSize;
    this.currentPage = ++currentPage;
    this.itemsTotal = itemsTotal;
    this.nextPage = this.currentPage + 1;
    this.previousPage = this.currentPage - 1;

    this.pageCount = function() {
        return Math.ceil(this.itemsTotal / this.pageSize);
    }

    this.canShowPrevious = function() {
        return ( this.currentPage > 1 );
    }

    this.canShowNext = function() {
        return ( this.currentPage < this.pageCount() );
    }

    this.canShowPrevious = this.canShowPrevious();
    this.canShowNext = this.canShowNext();
    this.pageCount = this.pageCount();
}
