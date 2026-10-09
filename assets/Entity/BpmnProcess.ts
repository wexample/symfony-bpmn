import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_process.json';

export default class BpmnProcess extends AbstractApiEntity {
  static readonly entityName = 'bpmnProcess';

  static retrieveEntitySchema() {
    return schema;
  }
}
